# 🛠️ DTO Utility Methods

Complete guide to DTO utility methods in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Immutable Updates](#immutable-updates)
3. [Comparison Methods](#comparison-methods)
4. [Merging DTOs](#merging-dtos)
5. [Cloning](#cloning)
6. [Conditional Serialization](#conditional-serialization)
7. [Best Practices](#best-practices)
8. [Real-World Examples](#real-world-examples)

---

## Introduction

The DTO class provides powerful utility methods for common operations like comparing, merging, cloning, and conditionally serializing DTOs.

### Available Methods

| Method | Purpose | Returns |
|--------|---------|---------|
| `with()` | Create new instance with updated values | `static` |
| `diff()` | Compare two DTOs and get differences | `array` |
| `equals()` | Check deep equality | `bool` |
| `hash()` | Generate hash for caching/comparison | `string` |
| `merge()` | Shallow merge with another DTO | `static` |
| `mergeRecursive()` | Deep merge with another DTO | `static` |
| `clone()` | Create deep copy | `static` |
| `replicate()` | Alias for clone() (Laravel-style) | `static` |
| `when()` | Conditional property inclusion | `array` |
| `unless()` | Inverse conditional inclusion | `array` |

---

## Immutable Updates

### with() - Update Properties

Create a new DTO instance with modified properties while keeping the original unchanged.

```php
use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly bool $active,
    ) {}
}

// Original DTO
$user = new UserDto(
    name: 'John Doe',
    email: 'john@example.com',
    active: false
);

// Create updated version
$activeUser = $user->with(['active' => true]);

echo $user->active;        // false (original unchanged)
echo $activeUser->active;  // true (new instance)
```

### Real-World Example: API Version Updates

```php
class ApiResponseDto extends Dto
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data,
        public readonly int $version = 1,
    ) {}
}

$response = ApiResponseDto::from([
    'success' => true,
    'data' => ['user_id' => 123]
]);

// Upgrade to v2 format
$v2Response = $response->with([
    'version' => 2,
    'data' => [
        'user' => ['id' => 123]  // Restructured for v2
    ]
]);
```

---

## Comparison Methods

### diff() - Find Differences

Compare two DTOs and get an array of differences.

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $stock,
    ) {}
}

$original = new ProductDto(
    name: 'Laptop',
    price: 999.99,
    stock: 50
);

$updated = new ProductDto(
    name: 'Laptop',
    price: 899.99,  // Price changed
    stock: 45        // Stock changed
);

$changes = $original->diff($updated);
print_r($changes);
```

**Output:**
```php
[
    'price' => ['old' => 999.99, 'new' => 899.99],
    'stock' => ['old' => 50, 'new' => 45],
]
```

### Deep Comparison with Nested DTOs

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}

class CustomerDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}
}

$customer1 = CustomerDto::from([
    'name' => 'John',
    'address' => ['street' => '123 Main St', 'city' => 'NYC']
]);

$customer2 = CustomerDto::from([
    'name' => 'John',
    'address' => ['street' => '123 Main St', 'city' => 'LA']  // City changed
]);

$diff = $customer1->diff($customer2, deep: true);
// Detects nested changes in address
```

### equals() - Check Equality

```php
$product1 = new ProductDto(name: 'Mouse', price: 29.99, stock: 100);
$product2 = new ProductDto(name: 'Mouse', price: 29.99, stock: 100);
$product3 = new ProductDto(name: 'Mouse', price: 39.99, stock: 100);

$product1->equals($product2);  // true
$product1->equals($product3);  // false
```

### hash() - Generate Cache Keys

```php
class CacheableDto extends Dto
{
    public function __construct(
        public readonly int $userId,
        public readonly string $resource,
    ) {}
}

$dto = new CacheableDto(userId: 123, resource: 'profile');
$cacheKey = 'dto_' . $dto->hash();

// Use for caching
$cache->set($cacheKey, $dto->toArray(), ttl: 3600);
```

---

## Merging DTOs

### merge() - Shallow Merge

Merge values from another DTO (shallow, top-level only).

```php
class ConfigDto extends Dto
{
    public function __construct(
        public readonly string $theme,
        public readonly string $language,
        public readonly int $timeout,
    ) {}
}

$defaults = new ConfigDto(
    theme: 'light',
    language: 'en',
    timeout: 30
);

$userPrefs = new ConfigDto(
    theme: 'dark',      // Override
    language: 'en',     // Keep default
    timeout: 60         // Override
);

$finalConfig = $defaults->merge($userPrefs);
// theme: 'dark', language: 'en', timeout: 60
```

### mergeRecursive() - Deep Merge

Recursively merge nested structures.

```php
class SettingsDto extends Dto
{
    public function __construct(
        public readonly array $ui,
        public readonly array $api,
    ) {}
}

$defaults = SettingsDto::from([
    'ui' => [
        'theme' => 'light',
        'fontSize' => 14,
        'sidebar' => 'visible'
    ],
    'api' => [
        'timeout' => 30,
        'retries' => 3
    ]
]);

$custom = SettingsDto::from([
    'ui' => [
        'theme' => 'dark',  // Override only theme
    ],
    'api' => [
        'timeout' => 60     // Override only timeout
    ]
]);

$merged = $defaults->mergeRecursive($custom);
// Result:
// ui: {theme: 'dark', fontSize: 14, sidebar: 'visible'}
// api: {timeout: 60, retries: 3}
```

---

## Cloning

### clone() - Deep Copy

Create an independent copy of a DTO.

```php
$order = OrderDto::from([
    'id' => 123,
    'items' => [
        ['product' => 'Laptop', 'qty' => 1],
        ['product' => 'Mouse', 'qty' => 2]
    ]
]);

$draftCopy = $order->clone();
// Completely independent copy, including nested arrays
```

### replicate() - Laravel-Style Alias

```php
// Same as clone(), Laravel developers will find this familiar
$copy = $order->replicate();
```

### Use Case: Audit Trail

```php
class AuditLog
{
    private array $history = [];
    
    public function snapshot(Dto $dto): void
    {
        // Store immutable copy
        $this->history[] = [
            'timestamp' => time(),
            'data' => $dto->clone()
        ];
    }
}
```

---

## Conditional Serialization

### when() - Include Properties When Condition is True

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $isAdmin,
    ) {}
    
    public function toArray(?Context $ctx = null): array
    {
        $base = parent::toArray($ctx);
        
        // Add admin-only fields conditionally
        return array_merge($base, $this->when($this->isAdmin, [
            'adminPanel' => '/admin',
            'permissions' => ['*']
        ]));
    }
}

$admin = new UserDto(id: 1, name: 'Admin', email: 'admin@example.com', isAdmin: true);
$user = new UserDto(id: 2, name: 'User', email: 'user@example.com', isAdmin: false);

$admin->toArray();
// Includes: id, name, email, isAdmin, adminPanel, permissions

$user->toArray();
// Includes: id, name, email, isAdmin (no adminPanel or permissions)
```

### unless() - Include Properties When Condition is False

```php
class ApiResponseDto extends Dto
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data,
        public readonly ?string $error = null,
    ) {}
    
    public function toArray(?Context $ctx = null): array
    {
        $base = parent::toArray($ctx);
        
        // Only include debug info in non-production
        return array_merge($base, $this->unless($this->isProduction(), [
            'debug' => [
                'memory' => memory_get_usage(),
                'time' => microtime(true)
            ]
        ]));
    }
    
    private function isProduction(): bool
    {
        return getenv('APP_ENV') === 'production';
    }
}
```

### Callable Variant

```php
public function toArray(?Context $ctx = null): array
{
    $base = parent::toArray($ctx);
    
    return array_merge($base, $this->when($this->hasPermission('view_sensitive'), function() {
        return [
            'ssn' => $this->ssn,
            'salary' => $this->salary,
        ];
    }));
}
```

---

## Best Practices

### 1. Use with() for Immutable Updates

✅ **DO:**
```php
$updated = $user->with(['email' => 'new@example.com']);
```

❌ **DON'T:**
```php
// DTOs are readonly, this won't work
$user->email = 'new@example.com';
```

### 2. Use diff() for Change Detection

```php
// Before saving, check if anything changed
if (empty($original->diff($updated))) {
    return; // No changes, skip save
}

$repository->save($updated);
```

### 3. Use hash() for Caching

```php
$cacheKey = "user_{$user->id}_{$user->hash()}";
if ($cached = $cache->get($cacheKey)) {
    return $cached;
}
```

### 4. Use clone() for Isolation

```php
// When processing in parallel or async
$tasks = array_map(
    fn($dto) => new ProcessTask($dto->clone()),
    $dtos
);
```

---

## Real-World Examples

### Example 1: Version Control System

```php
class DocumentDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        public readonly int $version,
    ) {}
}

class VersionControl
{
    private array $versions = [];
    
    public function save(DocumentDto $doc): void
    {
        $this->versions[] = $doc->clone();
    }
    
    public function getChanges(int $v1, int $v2): array
    {
        return $this->versions[$v1]->diff($this->versions[$v2]);
    }
    
    public function restore(int $version): DocumentDto
    {
        return $this->versions[$version]->clone();
    }
}
```

### Example 2: A/B Testing

```php
class FeatureConfigDto extends Dto
{
    public function __construct(
        public readonly string $variant,
        public readonly bool $newCheckout,
        public readonly bool $socialLogin,
    ) {}
}

$controlGroup = new FeatureConfigDto(
    variant: 'A',
    newCheckout: false,
    socialLogin: false
);

$testGroup = $controlGroup->with([
    'variant' => 'B',
    'newCheckout' => true,
    'socialLogin' => true
]);

// Compare performance
if ($testGroup->conversionRate > $controlGroup->conversionRate) {
    $winner = $testGroup;
}
```

### Example 3: Configuration Cascade

```php
// System defaults
$system = ConfigDto::from(['timeout' => 30, 'retries' => 3, 'debug' => false]);

// Environment overrides
$env = ConfigDto::from(['timeout' => 60]);

// User preferences
$user = ConfigDto::from(['debug' => true]);

// Cascade merge
$final = $system->merge($env)->merge($user);
// Result: timeout=60, retries=3, debug=true
```

---

## Summary

- ✅ **with()** - Immutable updates
- ✅ **diff()** - Change detection and audit trails
- ✅ **equals()** - Deep equality checks
- ✅ **hash()** - Caching and quick comparison
- ✅ **merge()/mergeRecursive()** - Configuration management
- ✅ **clone()/replicate()** - Create independent copies
- ✅ **when()/unless()** - Conditional serialization

---

**Next:** [Lifecycle Hooks](./lifecycle-hooks.md) | [Serialization Options](./serialization-options.md)
