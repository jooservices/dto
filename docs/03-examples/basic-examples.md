# 💡 Basic Examples

Practical examples to help you understand **jooservices/dto** usage.

---

## Example 1: Simple User DTO

The most basic use case - a user object.

```php
<?php

use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}

// Create from array
$user = UserDto::from([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

// Use it
echo "Name: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Age: {$user->age}\n";

// Convert to array
$array = $user->toArray();
print_r($array);
```

**Output:**
```
Name: John Doe
Email: john@example.com
Age: 30
Array
(
    [name] => John Doe
    [email] => john@example.com
    [age] => 30
)
```

---

## Example 2: Product with Price

Handling monetary values.

```php
<?php

use JOOservices\Dto\Core\Dto;

class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $sku,
        public readonly float $price,
        public readonly bool $inStock,
    ) {}
}

// Create from API response
$product = ProductDto::from([
    'name' => 'Wireless Mouse',
    'sku' => 'WM-001',
    'price' => '29.99',        // String → float
    'in_stock' => 1            // Int → bool
]);

// Display
echo "{$product->name} ({$product->sku})\n";
echo "Price: \${$product->price}\n";
echo "In Stock: " . ($product->inStock ? 'Yes' : 'No') . "\n";
```

**Output:**
```
Wireless Mouse (WM-001)
Price: $29.99
In Stock: Yes
```

---

## Example 3: DateTime Handling

Automatic DateTime conversion.

```php
<?php

use JOOservices\Dto\Core\Dto;

class EventDto extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly DateTime $startDate,
        public readonly DateTime $endDate,
    ) {}
}

// Create with date strings
$event = EventDto::from([
    'title' => 'Conference 2024',
    'start_date' => '2024-06-15 09:00:00',
    'end_date' => '2024-06-17 18:00:00'
]);

// Work with DateTime objects
echo "Event: {$event->title}\n";
echo "Starts: " . $event->startDate->format('M d, Y g:i A') . "\n";
echo "Ends: " . $event->endDate->format('M d, Y g:i A') . "\n";

$duration = $event->startDate->diff($event->endDate);
echo "Duration: {$duration->days} days\n";
```

**Output:**
```
Event: Conference 2024
Starts: Jun 15, 2024 9:00 AM
Ends: Jun 17, 2024 6:00 PM
Duration: 2 days
```

---

## Example 4: Nested Address

Objects within objects.

```php
<?php

use JOOservices\Dto\Core\Dto;

class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $state,
        public readonly string $zipCode,
    ) {}
}

class CustomerDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly AddressDto $address,
    ) {}
}

// Create with nested data
$customer = CustomerDto::from([
    'name' => 'Jane Smith',
    'phone' => '+1-555-0123',
    'address' => [
        'street' => '456 Oak Avenue',
        'city' => 'San Francisco',
        'state' => 'CA',
        'zip_code' => '94102'
    ]
]);

// Access nested properties
echo "{$customer->name}\n";
echo "{$customer->phone}\n";
echo "{$customer->address->street}\n";
echo "{$customer->address->city}, {$customer->address->state} {$customer->address->zipCode}\n";
```

**Output:**
```
Jane Smith
+1-555-0123
456 Oak Avenue
San Francisco, CA 94102
```

---

## Example 5: Collection of Items

Arrays of DTOs.

```php
<?php

use JOOservices\Dto\Core\Dto;

class OrderItemDto extends Dto
{
    public function __construct(
        public readonly string $productName,
        public readonly int $quantity,
        public readonly float $price,
    ) {
    }
    
    public function getTotal(): float
    {
        return $this->quantity * $this->price;
    }
}

class OrderDto extends Dto
{
    public function __construct(
        public readonly string $orderId,
        /** @var OrderItemDto[] */
        public readonly array $items,
    ) {}
    
    public function getOrderTotal(): float
    {
        return array_sum(array_map(
            fn(OrderItemDto $item) => $item->getTotal(),
            $this->items
        ));
    }
}

// Create order with items
$order = OrderDto::from([
    'order_id' => 'ORD-2024-001',
    'items' => [
        ['product_name' => 'Laptop', 'quantity' => 1, 'price' => 999.99],
        ['product_name' => 'Mouse', 'quantity' => 2, 'price' => 29.99],
        ['product_name' => 'Keyboard', 'quantity' => 1, 'price' => 79.99],
    ]
]);

// Display order
echo "Order ID: {$order->orderId}\n";
echo "Items:\n";

foreach ($order->items as $item) {
    printf(
        "  %s x%d @ $%.2f = $%.2f\n",
        $item->productName,
        $item->quantity,
        $item->price,
        $item->getTotal()
    );
}

printf("\nOrder Total: $%.2f\n", $order->getOrderTotal());
```

**Output:**
```
Order ID: ORD-2024-001
Items:
  Laptop x1 @ $999.99 = $999.99
  Mouse x2 @ $29.99 = $59.98
  Keyboard x1 @ $79.99 = $79.99

Order Total: $1139.96
```

---

## Example 6: Enum Support

Working with PHP enums.

```php
<?php

use JOOservices\Dto\Core\Dto;

enum Status: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}

class PostDto extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly Status $status,
    ) {}
}

// Create with enum
$post = PostDto::from([
    'title' => 'My First Post',
    'content' => 'Hello World!',
    'status' => 'published'  // String → Enum
]);

echo "Title: {$post->title}\n";
echo "Status: {$post->status->value}\n";

// Check status
if ($post->status === Status::PUBLISHED) {
    echo "✅ Post is live!\n";
}
```

**Output:**
```
Title: My First Post
Status: published
✅ Post is live!
```

---

## Example 7: Nullable Properties

Handling optional data.

```php
<?php

use JOOservices\Dto\Core\Dto;

class ProfileDto extends Dto
{
    public function __construct(
        public readonly string $username,
        public readonly ?string $bio,
        public readonly ?string $website,
        public readonly ?DateTime $lastLogin,
    ) {}
}

// With some nulls
$profile = ProfileDto::from([
    'username' => 'johndoe',
    'bio' => null,
    'website' => 'https://example.com',
    'last_login' => null
]);

echo "Username: {$profile->username}\n";
echo "Bio: " . ($profile->bio ?? 'Not provided') . "\n";
echo "Website: " . ($profile->website ?? 'Not provided') . "\n";
echo "Last Login: " . ($profile->lastLogin?->format('Y-m-d') ?? 'Never') . "\n";
```

**Output:**
```
Username: johndoe
Bio: Not provided
Website: https://example.com
Last Login: Never
```

---

## Example 8: Mutable Data Object

When you need to modify data.

```php
<?php

use JOOservices\Dto\Core\Data;

class FormData extends Data
{
    public string $name;
    public string $email;
    public string $message;
    public bool $subscribe = false;
}

// Create empty
$form = new FormData();

// Fill incrementally
$form->name = $_POST['name'] ?? '';
$form->email = $_POST['email'] ?? '';
$form->message = $_POST['message'] ?? '';
$form->subscribe = isset($_POST['subscribe']);

// Validate
$errors = [];
if (empty($form->name)) {
    $errors[] = 'Name is required';
}
if (!filter_var($form->email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email';
}

if (empty($errors)) {
    echo "Form submitted successfully!\n";
    echo "Name: {$form->name}\n";
    echo "Email: {$form->email}\n";
    echo "Subscribe: " . ($form->subscribe ? 'Yes' : 'No') . "\n";
} else {
    echo "Errors:\n";
    foreach ($errors as $error) {
        echo "  - $error\n";
    }
}
```

---

## Example 9: JSON API Response

Typical API response handling.

```php
<?php

use JOOservices\Dto\Core\Dto;

class ApiResponseDto extends Dto
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $message,
        public readonly mixed $data,
        public readonly ?array $errors,
    ) {}
}

// Parse API response
$jsonResponse = '{
    "success": true,
    "message": "User created successfully",
    "data": {
        "id": 123,
        "name": "John Doe",
        "email": "john@example.com"
    },
    "errors": null
}';

$response = ApiResponseDto::from(json_decode($jsonResponse, true));

if ($response->success) {
    echo "✅ Success: {$response->message}\n";
    print_r($response->data);
} else {
    echo "❌ Failed:\n";
    foreach ($response->errors ?? [] as $error) {
        echo "  - $error\n";
    }
}
```

**Output:**
```
✅ Success: User created successfully
Array
(
    [id] => 123
    [name] => John Doe
    [email] => john@example.com
)
```

---

## Example 10: snake_case to camelCase

Automatic naming strategy.

```php
<?php

use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $emailAddress,
    ) {}
}

// Input: snake_case (from database/API)
$user = UserDto::from([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email_address' => 'john@example.com'
]);

// Access: camelCase (PHP convention)
echo $user->firstName . ' ' . $user->lastName . "\n";
echo $user->emailAddress . "\n";

// Output: snake_case (for database/API)
$array = $user->toArray();
print_r($array);
```

**Output:**
```
John Doe
john@example.com
Array
(
    [first_name] => John
    [last_name] => Doe
    [email_address] => john@example.com
)
```

---

## 🎯 Next Examples

Ready for more? Check out:

- 🌐 [API Integration](./api-integration.md) - REST API examples
- 📝 [Form Handling](./form-handling.md) - Process form submissions
- 🗄️ [Database Mapping](./database-mapping.md) - ORM integration
- 🚀 [Real-World Scenarios](./real-world-scenarios.md) - Production use cases

---

## 💡 Tips

1. **Keep DTOs Simple** - One responsibility per DTO
2. **Use Type Hints** - Enable strict types
3. **Validate Early** - Check data at boundaries
4. **Document Complex Types** - Use `@var` for arrays
5. **Test Edge Cases** - Nulls, empty arrays, invalid data

---

**Need help?** See the [Troubleshooting Guide](../02-user-guide/troubleshooting.md) or [ask a question](https://github.com/jooservices/dto/discussions).
