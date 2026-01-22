# 🔄 Lifecycle Hooks

Complete guide to DTO lifecycle hooks in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Lifecycle Flow](#lifecycle-flow)
3. [transformInput()](#transforminput)
4. [afterHydration()](#afterhydration)
5. [beforeSerialization()](#beforeserialization)
6. [Real-World Examples](#real-world-examples)
7. [Best Practices](#best-practices)
8. [Security Considerations](#security-considerations)

---

## Introduction

DTO lifecycle hooks allow you to intercept and customize behavior at key points during a DTO's life:

| Hook | Context | When Called | Use Case |
|------|---------|-------------|----------|
| `transformInput()` | Static | Before instantiation | Sanitize, validate, transform raw input |
| `afterHydration()` | Instance | After construction | Cross-field validation, computed fields |
| `beforeSerialization()` | Instance | Before toArray()/toJson() | Hide sensitive data, add computed output |

---

## Lifecycle Flow

```
┌─────────────────┐
│  Input Data     │
└────────┬────────┘
         │
         ▼
┌─────────────────────────┐
│  transformInput()       │ ← Static method, modify raw data
│  (static context)       │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│  Hydration              │ ← Create DTO instance
│  (Constructor + Casting)│
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│  afterHydration()       │ ← Instance method, post-construction logic
│  (instance context)     │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│  DTO Instance Ready     │
└────────┬────────────────┘
         │
         ▼ (when serialized)
┌─────────────────────────┐
│  beforeSerialization()  │ ← Instance method, before output
│  (instance context)     │
└────────┬────────────────┘
         │
         ▼
┌─────────────────────────┐
│  toArray() / toJson()   │
└─────────────────────────┘
```

---

## transformInput()

**Static method called before DTO instantiation to transform/validate raw input data.**

### Signature

```php
protected static function transformInput(array $data): array
```

### When to Use

- Sanitize untrusted input (user forms, API requests)
- Normalize data formats
- Validate required fields before construction
- Transform API response to match DTO structure
- Hash passwords, encrypt data

### Example 1: Input Sanitization

```php
use JOOservices\Dto\Core\Dto;

class UserRegistrationDto extends Dto
{
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $password,
    ) {}
    
    protected static function transformInput(array $data): array
    {
        return [
            'username' => trim(strip_tags($data['username'] ?? '')),
            'email' => filter_var($data['email'] ?? '', FILTER_SANITIZE_EMAIL),
            'password' => $data['password'] ?? '',  // Will be hashed later
        ];
    }
}
```

### Example 2: API Response Transformation

```php
class GitHubUserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $avatarUrl,
    ) {}
    
    protected static function transformInput(array $data): array
    {
        // Transform GitHub API response to DTO format
        return [
            'id' => $data['id'],
            'username' => $data['login'],           // Map 'login' to 'username'
            'avatar_url' => $data['avatar_url'],
        ];
    }
}
```

### Example 3: Password Hashing

```php
class CreateUserDto extends Dto
{
    public function __construct(
        public readonly string $email,
        public readonly string $passwordHash,
    ) {}
    
    protected static function transformInput(array $data): array
    {
        if (empty($data['password']) || strlen($data['password']) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }
        
        return [
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        ];
    }
}
```

### Example 4: Date Normalization

```php
class EventDto extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly \DateTimeImmutable $startDate,
    ) {}
    
    protected static function transformInput(array $data): array
    {
        // Convert various date formats to ISO 8601
        $date = $data['start_date'];
        
        if (is_numeric($date)) {
            $date = date('Y-m-d H:i:s', $date);  // Unix timestamp to string
        } elseif (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
            $date = DateTime::createFromFormat('m/d/Y', $date)->format('Y-m-d');
        }
        
        return [
            'title' => $data['title'],
            'start_date' => $date,
        ];
    }
}
```

---

## afterHydration()

**Instance method called after DTO construction to perform post-hydration logic.**

### Signature

```php
protected function afterHydration(): void
```

### When to Use

- Cross-field validation (comparing multiple properties)
- Calculate computed/derived fields
- Initialize complex nested objects
- Trigger events or notifications
- Set up internal state

### Example 1: Cross-Field Validation

```php
class BookingDto extends Dto
{
    public function __construct(
        public readonly \DateTimeImmutable $checkIn,
        public readonly \DateTimeImmutable $checkOut,
        public readonly int $guests,
    ) {}
    
    protected function afterHydration(): void
    {
        // Validate check-out is after check-in
        if ($this->checkOut <= $this->checkIn) {
            throw new \DomainException('Check-out must be after check-in');
        }
        
        // Validate minimum stay
        $nights = $this->checkIn->diff($this->checkOut)->days;
        if ($nights < 1) {
            throw new \DomainException('Minimum stay is 1 night');
        }
        
        // Validate guest count
        if ($this->guests < 1 || $this->guests > 10) {
            throw new \DomainException('Guests must be between 1 and 10');
        }
    }
}
```

### Example 2: Computed Properties

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Attributes\Computed;

class PersonDto extends Dto implements ComputesLazyProperties
{
    public readonly int $age;
    
    public function __construct(
        public readonly string $name,
        public readonly \DateTimeImmutable $birthDate,
    ) {}
    
    protected function afterHydration(): void
    {
        // Calculate age after hydration
        $now = new \DateTimeImmutable();
        // Note: Can't directly set readonly property, use object property
        $reflection = new \ReflectionProperty($this, 'age');
        $reflection->setValue($this, $now->diff($this->birthDate)->y);
    }
    
    #[Computed]
    public function yearsUntilRetirement(): int
    {
        return max(0, 65 - $this->age);
    }
}
```

### Example 3: Business Rule Validation

```php
class OrderDto extends Dto
{
    public function __construct(
        public readonly float $subtotal,
        public readonly float $tax,
        public readonly float $shipping,
        public readonly float $total,
    ) {}
    
    protected function afterHydration(): void
    {
        // Verify total is correct
        $calculated = $this->subtotal + $this->tax + $this->shipping;
        
        if (abs($calculated - $this->total) > 0.01) {
            throw new \DomainException(
                sprintf('Total mismatch: expected %.2f, got %.2f', $calculated, $this->total)
            );
        }
    }
}
```

### Example 4: Initialize Relationships

```php
class PostDto extends Dto
{
    public array $comments = [];
    
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly array $commentIds,
    ) {}
    
    protected function afterHydration(): void
    {
        // Load related comments if needed
        if (!empty($this->commentIds)) {
            // Could lazy-load or pre-fetch comments here
            $this->comments = $this->loadComments($this->commentIds);
        }
    }
    
    private function loadComments(array $ids): array
    {
        // Fetch comments from repository
        return [];  // Placeholder
    }
}
```

---

## beforeSerialization()

**Instance method called before DTO is serialized to array/JSON.**

### Signature

```php
protected function beforeSerialization(): void
```

### When to Use

- Hide sensitive fields (passwords, tokens, keys)
- Add computed fields for output only
- Transform data for specific API versions
- Include metadata (timestamps, links)
- Apply output formatting

### Example 1: Hide Sensitive Data

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public string $passwordHash,
        public ?string $apiToken,
    ) {}
    
    protected function beforeSerialization(): void
    {
        // Remove sensitive fields before output
        $this->passwordHash = '[REDACTED]';
        $this->apiToken = null;
    }
}

$user = UserDto::from([
    'id' => 1,
    'email' => 'user@example.com',
    'password_hash' => '$2y$10$...',
    'api_token' => 'secret123'
]);

$array = $user->toArray();
// passwordHash: '[REDACTED]', apiToken: null
```

### Example 2: Add Computed Output Fields

```php
class ProductDto extends Dto
{
    public ?float $discount = null;
    public ?float $finalPrice = null;
    
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly float $discountPercent = 0,
    ) {}
    
    protected function beforeSerialization(): void
    {
        // Calculate output fields
        $this->discount = $this->price * ($this->discountPercent / 100);
        $this->finalPrice = $this->price - $this->discount;
    }
}

$product = new ProductDto(name: 'Laptop', price: 1000, discountPercent: 10);
$json = $product->toJson();
// Includes: discount: 100, finalPrice: 900
```

### Example 3: API Version Transformation

```php
class ApiResponseDto extends Dto
{
    public ?string $apiVersion = null;
    public ?array $links = null;
    
    public function __construct(
        public readonly int $id,
        public readonly mixed $data,
    ) {}
    
    protected function beforeSerialization(): void
    {
        // Add API metadata
        $this->apiVersion = 'v2.0';
        $this->links = [
            'self' => "/api/resources/{$this->id}",
            'related' => "/api/resources/{$this->id}/related"
        ];
    }
}
```

### Example 4: Conditional Output

```php
class UserProfileDto extends Dto
{
    public ?string $privateEmail = null;
    public ?string $phoneNumber = null;
    
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $phone,
        public readonly bool $isOwner = false,
    ) {}
    
    protected function beforeSerialization(): void
    {
        // Show sensitive info only if user is owner
        if ($this->isOwner) {
            $this->privateEmail = $this->email;
            $this->phoneNumber = $this->phone;
        } else {
            $this->privateEmail = null;
            $this->phoneNumber = null;
        }
    }
}
```

---

## Real-World Examples

### Example 1: Complete Registration Flow

```php
class UserRegistrationDto extends Dto
{
    private ?string $temporaryToken = null;
    
    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly \DateTimeImmutable $registeredAt,
    ) {}
    
    // 1. Transform raw input
    protected static function transformInput(array $data): array
    {
        // Validate and sanitize
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format');
        }
        
        return [
            'username' => trim($data['username']),
            'email' => strtolower($data['email']),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'registered_at' => new \DateTimeImmutable(),
        ];
    }
    
    // 2. Validate after construction
    protected function afterHydration(): void
    {
        // Check username length
        if (strlen($this->username) < 3 || strlen($this->username) > 20) {
            throw new \DomainException('Username must be 3-20 characters');
        }
        
        // Generate verification token
        $this->temporaryToken = bin2hex(random_bytes(32));
    }
    
    // 3. Clean up before output
    protected function beforeSerialization(): void
    {
        // Don't expose password hash in API response
        // temporaryToken is already excluded (private)
    }
}
```

### Example 2: Payment Processing

```php
class PaymentDto extends Dto
{
    public ?string $status = null;
    public ?string $receiptUrl = null;
    
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
        public string $cardNumber,
        public readonly string $cardholderName,
    ) {}
    
    protected static function transformInput(array $data): array
    {
        // Validate amount
        if ($data['amount'] <= 0) {
            throw new \InvalidArgumentException('Amount must be positive');
        }
        
        return $data;
    }
    
    protected function afterHydration(): void
    {
        // Validate currency
        $validCurrencies = ['USD', 'EUR', 'GBP'];
        if (!in_array($this->currency, $validCurrencies)) {
            throw new \DomainException('Invalid currency');
        }
        
        // Process payment (placeholder)
        $this->status = 'pending';
    }
    
    protected function beforeSerialization(): void
    {
        // Mask card number for security
        $this->cardNumber = 'XXXX-XXXX-XXXX-' . substr($this->cardNumber, -4);
        
        // Add receipt URL if payment successful
        if ($this->status === 'completed') {
            $this->receiptUrl = '/receipts/' . uniqid();
        }
    }
}
```

---

## Best Practices

### 1. Keep Hooks Focused

✅ **DO:**
```php
protected static function transformInput(array $data): array
{
    // Single purpose: sanitize input
    return [
        'email' => filter_var($data['email'], FILTER_SANITIZE_EMAIL),
        'name' => strip_tags($data['name']),
    ];
}
```

❌ **DON'T:**
```php
protected static function transformInput(array $data): array
{
    // Too many responsibilities
    $this->sendWelcomeEmail($data['email']);
    $this->logUserActivity($data);
    $this->updateStatistics();
    return $data;
}
```

### 2. Don't Mutate Readonly Properties

```php
// ❌ Can't do this - readonly properties
protected function afterHydration(): void
{
    $this->calculatedField = 123;  // Error!
}

// ✅ Use non-readonly properties or Computed
public ?int $calculatedField = null;

protected function afterHydration(): void
{
    $this->calculatedField = 123;  // OK
}
```

### 3. Handle Errors Gracefully

```php
protected static function transformInput(array $data): array
{
    if (empty($data['required_field'])) {
        throw new \InvalidArgumentException('required_field is missing');
    }
    
    return $data;
}
```

---

## Security Considerations

### 1. Always Sanitize External Input

```php
protected static function transformInput(array $data): array
{
    return [
        'comment' => htmlspecialchars($data['comment'], ENT_QUOTES, 'UTF-8'),
        'username' => preg_replace('/[^a-zA-Z0-9_]/', '', $data['username']),
    ];
}
```

### 2. Never Expose Sensitive Data

```php
protected function beforeSerialization(): void
{
    // Remove before serialization
    $this->passwordHash = null;
    $this->secretKey = null;
    $this->internalToken = null;
}
```

### 3. Validate Early

```php
protected static function transformInput(array $data): array
{
    // Validate BEFORE creating the DTO
    if ($data['age'] < 0 || $data['age'] > 150) {
        throw new \InvalidArgumentException('Invalid age');
    }
    
    return $data;
}
```

---

## Summary

- ✅ **transformInput()** - Clean, validate, transform raw input (static)
- ✅ **afterHydration()** - Cross-field validation, computed fields (instance)
- ✅ **beforeSerialization()** - Hide sensitive data, add output fields (instance)
- ✅ Keep hooks focused and single-purpose
- ✅ Handle errors gracefully with exceptions
- ✅ Always consider security implications

---

**Next:** [Utility Methods](./utility-methods.md) | [Serialization Options](./serialization-options.md)
