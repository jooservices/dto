# 🏗️ Creating DTOs

Complete guide to creating Data Transfer Objects with **jooservices/dto**.

---

## Table of Contents
1. [Basic DTO Structure](#basic-dto-structure)
2. [Property Types](#property-types)
3. [Constructor Property Promotion](#constructor-property-promotion)
4. [Nullable Properties](#nullable-properties)
5. [Default Values](#default-values)
6. [Type Hints](#type-hints)
7. [DocBlocks for Collections](#docblocks-for-collections)
8. [Best Practices](#best-practices)
9. [Common Patterns](#common-patterns)
10. [Anti-Patterns to Avoid](#anti-patterns-to-avoid)

---

## Basic DTO Structure

All immutable DTOs extend the `Dto` base class:

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

// Create from array
$user = UserDto::from([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);

// Access properties
echo $user->id;    // 1
echo $user->name;  // John Doe
```

### Key Points:
- ✅ Extend `Dto` base class
- ✅ Use `public readonly` properties
- ✅ Constructor property promotion
- ✅ Create via `from()` method

---

## Property Types

### Immutable DTOs (readonly)

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $price,
    ) {}
}

$product = ProductDto::from(['id' => 1, 'name' => 'Laptop', 'price' => 999.99]);
// $product->price = 1299.99; // ❌ Error: Cannot modify readonly property
```

### Mutable Data Objects

```php
use JOOservices\Dto\Core\Data;

class UserData extends Data
{
    public int $id;
    public string $name;
    public string $email;
}

$user = new UserData();
$user->id = 1;
$user->name = 'John';
$user->email = 'john@example.com'; // ✅ Can modify
```

---

## Constructor Property Promotion

PHP 8+ allows declaring and initializing properties in the constructor:

### Before (PHP 7):
```php
class UserDto extends Dto
{
    public readonly int $id;
    public readonly string $name;
    
    public function __construct(int $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }
}
```

### After (PHP 8+):
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}
```

✨ **Much cleaner!**

---

## Nullable Properties

Use `?` to mark properties as nullable:

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $nickname,    // Can be null
        public readonly ?string $bio,         // Can be null
        public readonly ?DateTime $lastLogin, // Can be null
    ) {}
}

$user = UserDto::from([
    'id' => 1,
    'name' => 'John Doe',
    'nickname' => null,
    'bio' => null,
    'last_login' => null
]);

// Safe null access
echo $user->nickname ?? 'No nickname';
echo $user->lastLogin?->format('Y-m-d') ?? 'Never logged in';
```

---

## Default Values

### Nullable with Null Default

```php
class ProfileDto extends Dto
{
    public function __construct(
        public readonly string $username,
        public readonly ?string $bio = null,        // Defaults to null
        public readonly ?string $website = null,    // Defaults to null
    ) {}
}

// Can omit nullable fields
$profile = ProfileDto::from([
    'username' => 'johndoe'
    // bio and website will be null
]);
```

### Non-Nullable with Default

```php
class SettingsDto extends Dto
{
    public function __construct(
        public readonly bool $notificationsEnabled = true,
        public readonly string $theme = 'light',
        public readonly int $itemsPerPage = 25,
    ) {}
}

$settings = SettingsDto::from([]);
echo $settings->theme; // 'light'
echo $settings->itemsPerPage; // 25
```

---

## Type Hints

### All Supported Types

```php
class CompleteDto extends Dto
{
    public function __construct(
        // Scalars
        public readonly int $id,
        public readonly float $price,
        public readonly string $name,
        public readonly bool $active,
        
        // DateTime
        public readonly DateTime $createdAt,
        public readonly ?DateTime $updatedAt,
        
        // Enums
        public readonly Status $status,
        
        // Objects (Nested DTOs)
        public readonly AddressDto $address,
        
        // Arrays
        public readonly array $tags,
        
        // Mixed (any type)
        public readonly mixed $metadata,
    ) {}
}
```

### Scalar Types

```php
class ScalarDto extends Dto
{
    public function __construct(
        public readonly int $integer,      // 123
        public readonly float $decimal,    // 99.99
        public readonly string $text,      // "Hello"
        public readonly bool $flag,        // true
    ) {}
}
```

### DateTime Types

```php
class EventDto extends Dto
{
    public function __construct(
        public readonly DateTime $startDate,
        public readonly DateTime $endDate,
        public readonly ?DateTime $canceledAt = null,
    ) {}
}

$event = EventDto::from([
    'start_date' => '2024-06-15 09:00:00',
    'end_date' => '2024-06-17 18:00:00'
]);
```

### Enum Types

```php
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
        public readonly Status $status,
    ) {}
}

$post = PostDto::from([
    'title' => 'My Post',
    'status' => 'published' // Automatically converts to Status enum
]);
```

### Nested Objects

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $zipCode,
    ) {}
}

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,  // Nested DTO
    ) {}
}

$user = UserDto::from([
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
        'zip_code' => '10001'
    ]
]);
```

---

## DocBlocks for Collections

Use DocBlocks to document array contents:

```php
class OrderDto extends Dto
{
    public function __construct(
        public readonly string $orderId,
        
        /** @var ProductDto[] Array of ProductDto objects */
        public readonly array $products,
        
        /** @var string[] Array of strings */
        public readonly array $tags,
        
        /** @var int[] Array of integers */
        public readonly array $ratings,
    ) {}
}
```

### Typed Collections Example

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
    ) {}
}

class OrderDto extends Dto
{
    public function __construct(
        public readonly string $id,
        /** @var ProductDto[] */
        public readonly array $products,
    ) {}
}

$order = OrderDto::from([
    'id' => 'ORD-123',
    'products' => [
        ['name' => 'Laptop', 'price' => 999.99],
        ['name' => 'Mouse', 'price' => 29.99],
    ]
]);

foreach ($order->products as $product) {
    echo "{$product->name}: \${$product->price}\n";
}
```

---

## Best Practices

### ✅ DO: Keep DTOs Simple

```php
// ✅ Good: Single responsibility
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}
```

```php
// ❌ Bad: Too many responsibilities
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly array $orders,      // Should be separate
        public readonly array $addresses,   // Should be separate
        public readonly array $payments,    // Should be separate
    ) {}
}
```

### ✅ DO: Use Readonly for Immutability

```php
// ✅ Good: Immutable
class UserDto extends Dto
{
    public function __construct(
        public readonly string $email,
    ) {}
}
```

```php
// ❌ Bad: Mutable DTO
class UserDto extends Dto
{
    public function __construct(
        public string $email,  // Missing readonly
    ) {}
}
```

### ✅ DO: Use Type Hints

```php
// ✅ Good: Strongly typed
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly bool $active,
    ) {}
}
```

```php
// ❌ Bad: No type hints
class UserDto extends Dto
{
    public function __construct(
        public readonly $id,      // What type?
        public readonly $name,    // What type?
        public readonly $active,  // What type?
    ) {}
}
```

### ✅ DO: Use Descriptive Names

```php
// ✅ Good: Clear naming
class UserProfileDto extends Dto
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $emailAddress,
    ) {}
}
```

```php
// ❌ Bad: Unclear naming
class UserDto extends Dto
{
    public function __construct(
        public readonly string $fn,    // What's fn?
        public readonly string $ln,    // What's ln?
        public readonly string $em,    // What's em?
    ) {}
}
```

---

## Common Patterns

### Pattern 1: Value Objects

```php
class Money extends Dto
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
    ) {}
    
    public function format(): string
    {
        return "{$this->currency} {$this->amount}";
    }
}

class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly Money $price,
    ) {}
}
```

### Pattern 2: Hierarchical DTOs

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}

class PersonDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}
}

class CompanyDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $headquarters,
        /** @var PersonDto[] */
        public readonly array $employees,
    ) {}
}
```

### Pattern 3: Request/Response DTOs

```php
class CreateUserRequestDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,
    ) {}
}

class UserResponseDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly DateTime $createdAt,
    ) {}
}
```

---

## Anti-Patterns to Avoid

### ❌ Anti-Pattern 1: God Objects

```php
// ❌ Bad: Too many responsibilities
class ApplicationDto extends Dto
{
    public function __construct(
        public readonly UserDto $user,
        public readonly array $settings,
        public readonly array $permissions,
        public readonly array $notifications,
        public readonly array $analytics,
        // ... 20 more properties
    ) {}
}
```

**Solution:** Split into focused DTOs

### ❌ Anti-Pattern 2: Mutable DTOs

```php
// ❌ Bad: Mutable when should be immutable
class ConfigDto extends Dto
{
    public function __construct(
        public string $apiKey,  // Missing readonly!
    ) {}
}

$config = ConfigDto::from(['api_key' => 'secret']);
$config->apiKey = 'hacked'; // ❌ Should not be possible!
```

**Solution:** Use `readonly`

### ❌ Anti-Pattern 3: Logic in DTOs

```php
// ❌ Bad: Business logic in DTO
class OrderDto extends Dto
{
    public function __construct(
        public readonly array $items,
        public readonly string $status,
    ) {}
    
    public function calculateTotal(): float
    {
        // ❌ Complex business logic doesn't belong here
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->price * $item->quantity;
            if ($item->discount > 0) {
                $total -= $total * ($item->discount / 100);
            }
        }
        return $total;
    }
}
```

**Solution:** Keep DTOs as data containers only

### ❌ Anti-Pattern 4: Inconsistent Naming

```php
// ❌ Bad: Mixed naming conventions
class UserDto extends Dto
{
    public function __construct(
        public readonly string $firstName,    // camelCase
        public readonly string $last_name,    // snake_case
        public readonly string $EmailAddress, // PascalCase
    ) {}
}
```

**Solution:** Use consistent camelCase

---

## Summary Checklist

When creating DTOs, ensure:

- [ ] ✅ Extends `Dto` base class
- [ ] ✅ Uses `public readonly` properties
- [ ] ✅ Uses constructor property promotion
- [ ] ✅ Has proper type hints
- [ ] ✅ Uses nullable (`?`) where appropriate
- [ ] ✅ Has DocBlocks for arrays
- [ ] ✅ Follows single responsibility
- [ ] ✅ Uses descriptive names
- [ ] ✅ Is immutable (no business logic)
- [ ] ✅ Is well-documented

---

## Next Steps

- 📖 [Type Casting](./type-casting.md) - Learn about automatic type conversion
- ✅ [Validation](./validation.md) - Add validation rules
- 🏗️ [Nested Objects](./nested-objects.md) - Work with complex structures
- 📚 [Best Practices](./best-practices.md) - Write better DTOs

---

**Questions?** See the [Troubleshooting Guide](./troubleshooting.md) or [ask on GitHub](https://github.com/jooservices/dto/discussions).
