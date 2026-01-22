# 🌍 Real-World Scenarios

Production-ready examples and patterns using **jooservices/dto**.

---

## Table of Contents
1. [REST API Development](#rest-api-development)
2. [Microservices Communication](#microservices-communication)
3. [Event Sourcing](#event-sourcing)
4. [API Client Library](#api-client-library)
5. [GraphQL Implementation](#graphql-implementation)
6. [CQRS Pattern](#cqrs-pattern)
7. [Data Import/Export](#data-importexport)

---

## REST API Development

### Request/Response DTOs

```php
// Request DTO
use JOOservices\Dto\Core\Data;
use JOOservices\Dto\Attributes\Validation\Required;

class CreateOrderRequest extends Data
{
    public function __construct(
        #[Required]
        public int $userId = 0,
        
        /** @var OrderItemRequest[] */
        #[Required]
        public array $items = [],
        
        public ?string $couponCode = null,
    ) {}
}

class OrderItemRequest extends Data
{
    public function __construct(
        #[Required]
        public int $productId = 0,
        
        #[Required]
        public int $quantity = 1,
    ) {}
}

// Response DTO
class OrderResponse extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $orderNumber,
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $discount,
        public readonly float $total,
        public readonly string $status,
        /** @var OrderItemResponse[] */
        public readonly array $items,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}

class OrderItemResponse extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $productName,
        public readonly int $quantity,
        public readonly float $unitPrice,
        public readonly float $total,
    ) {}
}
```

### API Controller

```php
class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService
    ) {}
    
    public function store(Request $request)
    {
        // Validate and hydrate request
        try {
            $requestDto = CreateOrderRequest::from(
                $request->all(),
                new Context(validationEnabled: true)
            );
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->getErrors(),
            ], 422);
        }
        
        // Process order
        $order = $this->orderService->createOrder($requestDto);
        
        // Return response DTO
        return response()->json(
            $order->toArray(),
            201
        );
    }
    
    public function show(int $id)
    {
        $order = $this->orderService->getOrder($id);
        
        if ($order === null) {
            return response()->json([
                'message' => 'Order not found'
            ], 404);
        }
        
        return response()->json($order->toArray());
    }
    
    public function index()
    {
        $orders = $this->orderService->getOrders();
        
        return response()->json([
            'data' => $orders->toArray(),
        ]);
    }
}
```

---

## Microservices Communication

### Service DTOs

```php
// User Service Response
class UserServiceDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly array $permissions,
    ) {}
}

// Product Service Response
class ProductServiceDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
        public readonly int $stock,
    ) {}
}

// Order Aggregate (combines data from multiple services)
class OrderAggregateDto extends Dto
{
    public function __construct(
        public readonly int $orderId,
        public readonly UserServiceDto $customer,
        /** @var OrderItemAggregateDto[] */
        public readonly array $items,
        public readonly float $total,
    ) {}
}

class OrderItemAggregateDto extends Dto
{
    public function __construct(
        public readonly ProductServiceDto $product,
        public readonly int $quantity,
        public readonly float $subtotal,
    ) {}
}
```

### Service Client

```php
class UserServiceClient
{
    public function __construct(
        private HttpClient $httpClient
    ) {}
    
    public function getUser(int $id): ?UserServiceDto
    {
        $response = $this->httpClient->get("/api/users/{$id}");
        
        if ($response->status() === 404) {
            return null;
        }
        
        return UserServiceDto::fromJson($response->body());
    }
}

class ProductServiceClient
{
    public function __construct(
        private HttpClient $httpClient
    ) {}
    
    public function getProduct(int $id): ?ProductServiceDto
    {
        $response = $this->httpClient->get("/api/products/{$id}");
        
        if ($response->status() === 404) {
            return null;
        }
        
        return ProductServiceDto::fromJson($response->body());
    }
}
```

### Aggregator Service

```php
class OrderAggregatorService
{
    public function __construct(
        private UserServiceClient $userService,
        private ProductServiceClient $productService,
        private OrderRepository $orderRepository,
    ) {}
    
    public function getOrderAggregate(int $orderId): ?OrderAggregateDto
    {
        // Get order from local database
        $order = $this->orderRepository->find($orderId);
        
        if ($order === null) {
            return null;
        }
        
        // Fetch customer from User Service
        $customer = $this->userService->getUser($order->user_id);
        
        if ($customer === null) {
            throw new \Exception("Customer not found");
        }
        
        // Fetch products for each item
        $items = [];
        foreach ($order->items as $item) {
            $product = $this->productService->getProduct($item['product_id']);
            
            if ($product !== null) {
                $items[] = OrderItemAggregateDto::from([
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'subtotal' => $product->price * $item['quantity'],
                ]);
            }
        }
        
        // Build aggregate
        return OrderAggregateDto::from([
            'order_id' => $order->id,
            'customer' => $customer,
            'items' => $items,
            'total' => $order->total,
        ]);
    }
}
```

---

## Event Sourcing

### Event DTOs

```php
abstract class DomainEventDto extends Dto
{
    public function __construct(
        public readonly string $eventId,
        public readonly string $aggregateId,
        public readonly string $eventType,
        public readonly DateTimeImmutable $occurredAt,
    ) {}
}

class UserRegisteredEventDto extends DomainEventDto
{
    public function __construct(
        string $eventId,
        string $aggregateId,
        DateTimeImmutable $occurredAt,
        public readonly string $email,
        public readonly string $name,
    ) {
        parent::__construct($eventId, $aggregateId, 'UserRegistered', $occurredAt);
    }
}

class OrderPlacedEventDto extends DomainEventDto
{
    public function __construct(
        string $eventId,
        string $aggregateId,
        DateTimeImmutable $occurredAt,
        public readonly int $userId,
        /** @var array<int, int> Product ID => Quantity */
        public readonly array $items,
        public readonly float $total,
    ) {
        parent::__construct($eventId, $aggregateId, 'OrderPlaced', $occurredAt);
    }
}
```

### Event Store

```php
class EventStore
{
    public function append(DomainEventDto $event): void
    {
        DB::table('events')->insert([
            'event_id' => $event->eventId,
            'aggregate_id' => $event->aggregateId,
            'event_type' => $event->eventType,
            'event_data' => json_encode($event->toArray()),
            'occurred_at' => $event->occurredAt,
        ]);
    }
    
    public function getEvents(string $aggregateId): array
    {
        $records = DB::table('events')
            ->where('aggregate_id', $aggregateId)
            ->orderBy('occurred_at')
            ->get();
        
        return $records->map(function ($record) {
            $data = json_decode($record->event_data, true);
            
            return match ($record->event_type) {
                'UserRegistered' => UserRegisteredEventDto::from($data),
                'OrderPlaced' => OrderPlacedEventDto::from($data),
                default => throw new \Exception("Unknown event type"),
            };
        })->toArray();
    }
}
```

---

## API Client Library

### Client DTOs

```php
// Stripe API Client Example

class StripeCustomerDto extends Dto
{
    public function __construct(
        public readonly string $id,
        public readonly string $email,
        public readonly string $name,
        public readonly DateTimeImmutable $created,
    ) {}
}

class StripePaymentIntentDto extends Dto
{
    public function __construct(
        public readonly string $id,
        public readonly int $amount,
        public readonly string $currency,
        public readonly string $status,
        public readonly ?string $clientSecret,
    ) {}
}

class CreatePaymentIntentRequest extends Data
{
    public function __construct(
        public int $amount = 0,
        public string $currency = 'usd',
        public ?string $customerId = null,
        public array $metadata = [],
    ) {}
}
```

### API Client

```php
class StripeClient
{
    public function __construct(
        private string $apiKey,
        private HttpClient $httpClient
    ) {}
    
    public function createCustomer(
        string $email,
        string $name
    ): StripeCustomerDto {
        $response = $this->httpClient->post('/v1/customers', [
            'email' => $email,
            'name' => $name,
        ], [
            'Authorization' => "Bearer {$this->apiKey}",
        ]);
        
        return StripeCustomerDto::fromJson($response->body());
    }
    
    public function createPaymentIntent(
        CreatePaymentIntentRequest $request
    ): StripePaymentIntentDto {
        $response = $this->httpClient->post(
            '/v1/payment_intents',
            $request->toArray(),
            [
                'Authorization' => "Bearer {$this->apiKey}",
            ]
        );
        
        return StripePaymentIntentDto::fromJson($response->body());
    }
    
    public function getPaymentIntent(string $id): StripePaymentIntentDto
    {
        $response = $this->httpClient->get("/v1/payment_intents/{$id}", [
            'Authorization' => "Bearer {$this->apiKey}",
        ]);
        
        return StripePaymentIntentDto::fromJson($response->body());
    }
}
```

---

## GraphQL Implementation

### GraphQL Types as DTOs

```php
class UserType extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        /** @var PostType[] */
        public readonly array $posts,
    ) {}
}

class PostType extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly UserType $author,
        /** @var CommentType[] */
        public readonly array $comments,
    ) {}
}

class CommentType extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $text,
        public readonly UserType $author,
    ) {}
}
```

### GraphQL Resolvers

```php
class UserResolver
{
    public function __construct(
        private UserRepository $userRepository,
        private PostRepository $postRepository,
    ) {}
    
    public function resolve(array $args): ?UserType
    {
        $user = $this->userRepository->find($args['id']);
        
        if ($user === null) {
            return null;
        }
        
        $posts = $this->postRepository->findByUserId($user->id);
        
        return UserType::from([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'posts' => $posts->toArray(),
        ]);
    }
}
```

---

## CQRS Pattern

### Commands

```php
class CreateUserCommand extends Data
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $password = '',
    ) {}
}

class UpdateUserCommand extends Data
{
    public function __construct(
        public int $id = 0,
        public string $name = '',
        public string $email = '',
    ) {}
}
```

### Queries

```php
class GetUserQuery extends Dto
{
    public function __construct(
        public readonly int $id,
    ) {}
}

class ListUsersQuery extends Dto
{
    public function __construct(
        public readonly int $page = 1,
        public readonly int $perPage = 10,
        public readonly ?string $search = null,
    ) {}
}
```

### Command Handler

```php
class CreateUserCommandHandler
{
    public function __construct(
        private UserRepository $repository,
        private EventBus $eventBus,
    ) {}
    
    public function handle(CreateUserCommand $command): UserDto
    {
        $user = User::create([
            'name' => $command->name,
            'email' => $command->email,
            'password' => bcrypt($command->password),
        ]);
        
        // Dispatch event
        $this->eventBus->dispatch(
            UserCreatedEventDto::from([
                'user_id' => $user->id,
                'email' => $user->email,
            ])
        );
        
        return UserMapper::toDto($user);
    }
}
```

### Query Handler

```php
class GetUserQueryHandler
{
    public function __construct(
        private UserRepository $repository
    ) {}
    
    public function handle(GetUserQuery $query): ?UserDto
    {
        return $this->repository->find($query->id);
    }
}

class ListUsersQueryHandler
{
    public function __construct(
        private UserRepository $repository
    ) {}
    
    public function handle(ListUsersQuery $query): PaginatedCollection
    {
        return $this->repository->paginate(
            page: $query->page,
            perPage: $query->perPage,
            search: $query->search
        );
    }
}
```

---

## Data Import/Export

### CSV Import

```php
class ImportUserData extends Data
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public ?DateTimeImmutable $birthDate = null,
        public string $country = '',
    ) {}
    
    public static function fromCsvRow(array $row): self
    {
        return self::from([
            'name' => $row[0] ?? '',
            'email' => $row[1] ?? '',
            'birth_date' => isset($row[2]) ? $row[2] : null,
            'country' => $row[3] ?? '',
        ]);
    }
}

class CsvImportService
{
    public function import(string $filePath): array
    {
        $file = fopen($filePath, 'r');
        $imported = 0;
        $errors = [];
        
        // Skip header row
        fgetcsv($file);
        
        while (($row = fgetcsv($file)) !== false) {
            try {
                $userData = ImportUserData::fromCsvRow($row);
                
                User::create($userData->toArray());
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($imported + count($errors) + 2) . ": " . $e->getMessage();
            }
        }
        
        fclose($file);
        
        return [
            'imported' => $imported,
            'errors' => $errors,
        ];
    }
}
```

### JSON Export

```php
class ExportUserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $country,
        public readonly DateTimeImmutable $registeredAt,
    ) {}
}

class JsonExportService
{
    public function export(array $userIds): string
    {
        $users = User::whereIn('id', $userIds)->get();
        
        $collection = new DataCollection(
            ExportUserDto::class,
            $users->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'country' => $user->country,
                'registered_at' => $user->created_at->toImmutable(),
            ])->toArray()
        );
        
        return $collection->toJson(JSON_PRETTY_PRINT);
    }
}
```

---

## Summary

- ✅ **REST APIs** with type-safe request/response DTOs
- ✅ **Microservices** communication and data aggregation
- ✅ **Event Sourcing** with immutable event DTOs
- ✅ **API clients** with consistent data structures
- ✅ **GraphQL** type definitions as DTOs
- ✅ **CQRS** pattern with command/query DTOs
- ✅ **Import/Export** with data transformation

---

**Next:** [API Integration](./api-integration.md) | [Basic Examples](./basic-examples.md)
