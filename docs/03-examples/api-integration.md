# 🌐 API Integration Examples

Real-world examples of using DTOs with REST APIs.

---

## Example 1: Single User Response

### API Response (JSON):
```json
{
  "id": 123,
  "name": "John Doe",
  "email": "john@example.com",
  "created_at": "2024-01-20T10:30:00Z"
}
```

### DTO Definition:
```php
<?php

use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly DateTime $createdAt,
    ) {}
}
```

### Usage:
```php
$response = file_get_contents('https://api.example.com/users/123');
$data = json_decode($response, true);

$user = UserDto::from($data);

echo "User: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Joined: " . $user->createdAt->format('Y-m-d') . "\n";
```

---

## Example 2: Collection/List Response

### API Response:
```json
{
  "data": [
    {"id": 1, "name": "John Doe", "email": "john@example.com"},
    {"id": 2, "name": "Jane Smith", "email": "jane@example.com"},
    {"id": 3, "name": "Bob Johnson", "email": "bob@example.com"}
  ],
  "total": 3
}
```

### DTOs:
```php
<?php

use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}

class UsersResponseDto extends Dto
{
    public function __construct(
        /** @var UserDto[] */
        public readonly array $data,
        public readonly int $total,
    ) {}
}
```

### Usage:
```php
$response = file_get_contents('https://api.example.com/users');
$json = json_decode($response, true);

$usersResponse = UsersResponseDto::from($json);

echo "Total users: {$usersResponse->total}\n";

foreach ($usersResponse->data as $user) {
    echo "- {$user->name} ({$user->email})\n";
}
```

---

## Example 3: POST Request Payload

### Create User Request:
```php
<?php

use JOOservices\Dto\Core\Dto;

class CreateUserRequestDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}
}

// Build request
$request = new CreateUserRequestDto(
    name: 'John Doe',
    email: 'john@example.com',
    password: 'secure_password_123'
);

// Convert to JSON for API
$jsonPayload = json_encode($request->toArray());

// Send to API
$ch = curl_init('https://api.example.com/users');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($statusCode === 201) {
    $userData = json_decode($response, true);
    $user = UserDto::from($userData);
    echo "User created: {$user->id}\n";
}
```

---

## Example 4: Error Response Handling

### API Error Response:
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Invalid email format",
    "fields": {
      "email": ["Must be a valid email address"]
    }
  }
}
```

### DTOs:
```php
<?php

use JOOservices\Dto\Core\Dto;

class ApiErrorDto extends Dto
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?array $fields = null,
    ) {}
}

class ApiResponseDto extends Dto
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data = null,
        public readonly ?ApiErrorDto $error = null,
    ) {}
}
```

### Usage:
```php
$response = file_get_contents('https://api.example.com/users');
$json = json_decode($response, true);

$apiResponse = ApiResponseDto::from($json);

if ($apiResponse->success) {
    $user = UserDto::from($apiResponse->data);
    echo "Success: {$user->name}\n";
} else {
    echo "Error: {$apiResponse->error->message}\n";
    if ($apiResponse->error->fields) {
        foreach ($apiResponse->error->fields as $field => $errors) {
            echo "  {$field}: " . implode(', ', $errors) . "\n";
        }
    }
}
```

---

## Example 5: Pagination

### API Response with Pagination:
```json
{
  "data": [
    {"id": 1, "title": "Post 1"},
    {"id": 2, "title": "Post 2"}
  ],
  "pagination": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5
  }
}
```

### DTOs:
```php
<?php

use JOOservices\Dto\Core\Dto;

class PostDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
    ) {}
}

class PaginationDto extends Dto
{
    public function __construct(
        public readonly int $currentPage,
        public readonly int $perPage,
        public readonly int $total,
        public readonly int $lastPage,
    ) {}
    
    public function hasMorePages(): bool
    {
        return $this->currentPage < $this->lastPage;
    }
}

class PaginatedResponseDto extends Dto
{
    public function __construct(
        /** @var PostDto[] */
        public readonly array $data,
        public readonly PaginationDto $pagination,
    ) {}
}
```

### Usage:
```php
function fetchPosts(int $page = 1): PaginatedResponseDto
{
    $response = file_get_contents("https://api.example.com/posts?page={$page}");
    return PaginatedResponseDto::from(json_decode($response, true));
}

// Fetch all pages
$allPosts = [];
$page = 1;

do {
    $response = fetchPosts($page);
    $allPosts = array_merge($allPosts, $response->data);
    $page++;
} while ($response->pagination->hasMorePages());

echo "Fetched " . count($allPosts) . " posts\n";
```

---

## Example 6: Nested API Response

### Complex API Response:
```json
{
  "user": {
    "id": 123,
    "name": "John Doe",
    "email": "john@example.com"
  },
  "address": {
    "street": "123 Main St",
    "city": "New York",
    "country": "USA"
  },
  "orders": [
    {
      "id": "ORD-001",
      "total": 99.99,
      "status": "shipped"
    }
  ]
}
```

### DTOs:
```php
<?php

use JOOservices\Dto\Core\Dto;

class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
    ) {}
}

class OrderDto extends Dto
{
    public function __construct(
        public readonly string $id,
        public readonly float $total,
        public readonly string $status,
    ) {}
}

class UserProfileDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}

class UserDetailsResponseDto extends Dto
{
    public function __construct(
        public readonly UserProfileDto $user,
        public readonly AddressDto $address,
        /** @var OrderDto[] */
        public readonly array $orders,
    ) {}
}
```

### Usage:
```php
$response = file_get_contents('https://api.example.com/users/123/details');
$data = json_decode($response, true);

$userDetails = UserDetailsResponseDto::from($data);

echo "User: {$userDetails->user->name}\n";
echo "Location: {$userDetails->address->city}, {$userDetails->address->country}\n";
echo "Orders: " . count($userDetails->orders) . "\n";

foreach ($userDetails->orders as $order) {
    echo "  - {$order->id}: \${$order->total} ({$order->status})\n";
}
```

---

## Example 7: Response with Metadata

### API Response:
```json
{
  "data": {
    "id": 123,
    "name": "John Doe"
  },
  "meta": {
    "request_id": "req_abc123",
    "timestamp": "2024-01-20T10:30:00Z",
    "version": "v1"
  }
}
```

### DTOs:
```php
<?php

use JOOservices\Dto\Core\Dto;

class MetaDto extends Dto
{
    public function __construct(
        public readonly string $requestId,
        public readonly DateTime $timestamp,
        public readonly string $version,
    ) {}
}

class ApiResponseWithMetaDto extends Dto
{
    public function __construct(
        public readonly mixed $data,
        public readonly MetaDto $meta,
    ) {}
}
```

### Usage:
```php
$response = file_get_contents('https://api.example.com/users/123');
$json = json_decode($response, true);

$apiResponse = ApiResponseWithMetaDto::from($json);
$user = UserDto::from($apiResponse->data);

echo "User: {$user->name}\n";
echo "Request ID: {$apiResponse->meta->requestId}\n";
echo "API Version: {$apiResponse->meta->version}\n";
```

---

## Example 8: Complete API Client

### Full API Client Implementation:
```php
<?php

use JOOservices\Dto\Core\Dto;

class ApiClient
{
    public function __construct(
        private string $baseUrl,
        private string $apiKey,
    ) {}
    
    public function getUser(int $id): UserDto
    {
        $response = $this->get("/users/{$id}");
        return UserDto::from($response);
    }
    
    public function listUsers(int $page = 1): PaginatedResponseDto
    {
        $response = $this->get("/users?page={$page}");
        return PaginatedResponseDto::from($response);
    }
    
    public function createUser(CreateUserRequestDto $request): UserDto
    {
        $response = $this->post('/users', $request->toArray());
        return UserDto::from($response);
    }
    
    private function get(string $endpoint): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Accept: application/json',
        ]);
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($statusCode >= 400) {
            throw new \Exception("API Error: {$statusCode}");
        }
        
        return json_decode($response, true);
    }
    
    private function post(string $endpoint, array $data): array
    {
        $ch = curl_init($this->baseUrl . $endpoint);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($statusCode >= 400) {
            throw new \Exception("API Error: {$statusCode}");
        }
        
        return json_decode($response, true);
    }
}

// Usage
$client = new ApiClient('https://api.example.com', 'your-api-key');

// Get single user
$user = $client->getUser(123);
echo "User: {$user->name}\n";

// List users
$response = $client->listUsers(page: 1);
foreach ($response->data as $user) {
    echo "- {$user->name}\n";
}

// Create user
$newUser = $client->createUser(new CreateUserRequestDto(
    name: 'Jane Doe',
    email: 'jane@example.com',
    password: 'secure_password'
));
echo "Created user: {$newUser->id}\n";
```

---

## Best Practices

### 1. **Handle Errors Gracefully**
```php
try {
    $user = UserDto::from($apiResponse);
} catch (\Exception $e) {
    // Log error, show user-friendly message
    error_log("Failed to parse API response: " . $e->getMessage());
    throw new ApiException("Unable to process user data");
}
```

### 2. **Validate API Responses**
```php
class ApiResponseDto extends Dto
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data,
    ) {}
    
    public function getDataOrFail(): mixed
    {
        if (!$this->success) {
            throw new \Exception("API request failed");
        }
        return $this->data;
    }
}
```

### 3. **Use Type-Specific DTOs**
```php
// ✅ Good: Separate request/response DTOs
class CreateUserRequestDto extends Dto { /* ... */ }
class UserResponseDto extends Dto { /* ... */ }

// ❌ Bad: Single DTO for everything
class UserDto extends Dto { /* ... */ }
```

### 4. **Cache Metadata**
```php
class CachedApiResponse extends Dto
{
    public function __construct(
        public readonly mixed $data,
        public readonly DateTime $cachedAt,
        public readonly int $ttl,
    ) {}
    
    public function isExpired(): bool
    {
        $expiresAt = clone $this->cachedAt;
        $expiresAt->modify("+{$this->ttl} seconds");
        return new DateTime() > $expiresAt;
    }
}
```

---

## Tips

💡 **Tip 1:** Use DTOs for request validation before sending to API  
💡 **Tip 2:** Create dedicated DTOs for different API endpoints  
💡 **Tip 3:** Handle null/optional fields with nullable types  
💡 **Tip 4:** Use enums for API status codes/types  
💡 **Tip 5:** Add helper methods for common operations  

---

## Next Examples

- 📝 [Form Handling](./form-handling.md) - Process form submissions
- 🗄️ [Database Mapping](./database-mapping.md) - ORM integration
- 🚀 [Real-World Scenarios](./real-world-scenarios.md) - Production use cases

---

**Questions?** See the [Troubleshooting Guide](../02-user-guide/troubleshooting.md) or [ask on GitHub](https://github.com/jooservices/dto/discussions).
