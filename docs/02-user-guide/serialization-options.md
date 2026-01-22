# 📤 Serialization Options & Context

Complete guide to controlling serialization and hydration in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [SerializationOptions](#serializationoptions)
3. [Context](#context)
4. [Cast Modes](#cast-modes)
5. [Real-World Examples](#real-world-examples)
6. [Best Practices](#best-practices)

---

## Introduction

The library provides two key classes for controlling behavior:

- **SerializationOptions** - Control what/how DTOs serialize to arrays/JSON
- **Context** - Control hydration, validation, casting, and serialization globally

```php
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;

// Create context with options
$options = new SerializationOptions(only: ['name', 'email']);
$context = new Context(serializationOptions: $options);

// Use with serialization
$array = $dto->toArray($context);
```

---

## SerializationOptions

Control which properties are included in serialized output and how deep to traverse.

### Available Options

| Option | Type | Default | Purpose |
|--------|------|---------|---------|
| `only` | `array\|null` | `null` | Whitelist: include only these properties |
| `except` | `array\|null` | `null` | Blacklist: exclude these properties |
| `maxDepth` | `int` | `10` | Maximum nesting depth |
| `includeLazy` | `array\|null` | `null` | Include computed/lazy properties |
| `wrap` | `string\|null` | `null` | Wrap output in a key |

### 1. Whitelist with `only`

Include only specific properties.

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\SerializationOptions;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $apiToken,
    ) {}
}

$user = UserDto::from([
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password_hash' => '$2y$10$...',
    'api_token' => 'secret123'
]);

// Include only safe fields
$options = new SerializationOptions(only: ['id', 'name', 'email']);
$safeData = $user->toArray($options);

print_r($safeData);
```

**Output:**
```php
[
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    // passwordHash and apiToken excluded
]
```

### 2. Blacklist with `except`

Exclude specific properties.

```php
// Exclude sensitive fields
$options = new SerializationOptions(except: ['password_hash', 'api_token']);
$publicData = $user->toArray($options);

print_r($publicData);
```

**Output:**
```php
[
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'john@example.com',
    // passwordHash and apiToken excluded
]
```

### 3. Control Depth with `maxDepth`

Prevent infinite recursion and control nesting levels.

```php
class CommentDto extends Dto
{
    public function __construct(
        public readonly string $text,
        public readonly ?CommentDto $parent = null,
    ) {}
}

// Deep nesting
$comment3 = new CommentDto(text: 'Reply to reply', parent: null);
$comment2 = new CommentDto(text: 'Reply', parent: $comment3);
$comment1 = new CommentDto(text: 'Original', parent: $comment2);

// Limit depth to 2 levels
$options = new SerializationOptions(maxDepth: 2);
$limited = $comment1->toArray($options);
// Only serializes 2 levels deep
```

### 4. Include Lazy Properties with `includeLazy`

```php
use JOOservices\Dto\Attributes\Computed;
use JOOservices\Dto\Core\ComputesLazyProperties;

class ProductDto extends Dto implements ComputesLazyProperties
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $stock,
    ) {}
    
    #[Computed]
    public function inStock(): bool
    {
        return $this->stock > 0;
    }
    
    #[Computed]
    public function value(): float
    {
        return $this->price * $this->stock;
    }
}

$product = new ProductDto(name: 'Laptop', price: 999.99, stock: 50);

// Default: lazy properties NOT included
$basic = $product->toArray();
// {name, price, stock}

// Include all lazy properties
$options = new SerializationOptions(includeLazy: []);
$withLazy = $product->toArray($options);
// {name, price, stock, inStock, value}

// Include specific lazy properties
$options = new SerializationOptions(includeLazy: ['inStock']);
$selective = $product->toArray($options);
// {name, price, stock, inStock}
```

### 5. Wrap Output with `wrap`

Wrap serialized data in a key (common for APIs).

```php
$user = UserDto::from(['id' => 1, 'name' => 'John']);

$options = new SerializationOptions(wrap: 'data');
$wrapped = $user->toArray($options);

print_r($wrapped);
```

**Output:**
```php
[
    'data' => [
        'id' => 1,
        'name' => 'John',
    ]
]
```

### Builder Methods

Chain configuration fluently:

```php
$options = (new SerializationOptions())
    ->withOnly(['id', 'name', 'email'])
    ->withMaxDepth(3)
    ->withWrap('user');

$result = $dto->toArray($options);
```

---

## Context

Control hydration, validation, casting, and serialization globally.

### Available Options

| Option | Type | Default | Purpose |
|--------|------|---------|---------|
| `namingStrategy` | `NamingStrategyInterface\|null` | `null` | Property name conversion |
| `validationEnabled` | `bool` | `false` | Enable validation during hydration |
| `serializationOptions` | `SerializationOptions\|null` | `null` | Serialization behavior |
| `transformerMode` | `string` | `'full'` | Transformer application mode |
| `customData` | `array` | `[]` | Custom context data |
| `castMode` | `string` | `'loose'` | Type casting strictness |
| `globalPipeline` | `array` | `[]` | Global transformation pipeline |

### 1. Naming Strategies

Automatically convert property names between conventions.

```php
use JOOservices\Dto\Hydration\Naming\SnakeCaseStrategy;
use JOOservices\Dto\Hydration\Naming\CamelCaseStrategy;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $emailAddress,
    ) {}
}

// Convert snake_case input to camelCase properties
$context = new Context(namingStrategy: new SnakeCaseStrategy());

$user = UserDto::from([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'email_address' => 'john@example.com'
], $context);

echo $user->firstName;  // John
```

### 2. Enable Validation

```php
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\Email;

class ContactDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $name,
        
        #[Required]
        #[Email]
        public readonly string $email,
    ) {}
}

// Validation DISABLED by default
$dto = ContactDto::from(['name' => 'John']);  // No error

// Enable validation
$context = new Context(validationEnabled: true);
$dto = ContactDto::from(['name' => 'John'], $context);  // Throws ValidationException
```

### 3. Combine with SerializationOptions

```php
$options = new SerializationOptions(except: ['password']);
$context = new Context(
    validationEnabled: true,
    serializationOptions: $options
);

// Validation on hydration
$user = UserDto::from($data, $context);

// Serialization with options
$safe = $user->toArray($context);
```

### 4. Custom Context Data

Pass custom data through the hydration/serialization pipeline.

```php
$context = new Context(customData: [
    'tenant_id' => 123,
    'user_id' => 456,
    'timestamp' => time()
]);

// Access in custom casters/validators
$value = $context->getCustom('tenant_id');
```

### Builder Methods

```php
$context = (new Context())
    ->withValidationEnabled(true)
    ->withNamingStrategy(new SnakeCaseStrategy())
    ->withCustomData(['tenant' => 123])
    ->wrap('data');  // Shortcut for serialization wrap
```

---

## Cast Modes

Control how strictly types are enforced during hydration.

### 1. Loose Mode (Default)

Attempts type conversion, lenient on mismatches.

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly float $price,
    ) {}
}

// Loose mode converts strings to numbers
$product = ProductDto::from([
    'id' => '123',      // string → int
    'price' => '99.99'  // string → float
]);

echo $product->id;     // 123 (int)
echo $product->price;  // 99.99 (float)
```

### 2. Strict Mode

Throws exceptions on type mismatches.

```php
$context = new Context(castMode: 'strict');

// This will throw CastException
$product = ProductDto::from([
    'id' => '123',  // ❌ string is not int
], $context);
```

### 3. Permissive Mode

Most lenient: failed casts return null instead of throwing.

```php
$context = Context::permissive();

$product = ProductDto::from([
    'id' => 'invalid',  // Can't cast to int
    'price' => 'abc'     // Can't cast to float
], $context);

// No exception, but values may be null or 0
```

### Per-Property Strict Mode

Force strict casting for specific properties:

```php
use JOOservices\Dto\Attributes\StrictType;

class OrderDto extends Dto
{
    public function __construct(
        public readonly int $id,
        
        #[StrictType]  // Always strict, regardless of context
        public readonly float $total,
    ) {}
}

// Global loose mode, but total is strict
$order = OrderDto::from([
    'id' => '123',      // ✅ Converted (loose)
    'total' => '99.99'  // ❌ Throws (strict)
]);
```

---

## Real-World Examples

### Example 1: API Response with Pagination

```php
class PaginatedResponseDto extends Dto
{
    public function __construct(
        public readonly array $data,
        public readonly int $page,
        public readonly int $perPage,
        public readonly int $total,
    ) {}
}

$response = new PaginatedResponseDto(
    data: $users,
    page: 1,
    perPage: 10,
    total: 100
);

// Wrap in standard API format
$options = new SerializationOptions(wrap: 'response');
$json = $response->toJson($options);

// Output: {"response": {"data": [...], "page": 1, ...}}
```

### Example 2: Multi-Tenant Application

```php
class TenantContext
{
    public static function create(int $tenantId): Context
    {
        return new Context(
            validationEnabled: true,
            customData: [
                'tenant_id' => $tenantId,
                'timestamp' => time()
            ]
        );
    }
}

$context = TenantContext::create(tenantId: 123);
$order = OrderDto::from($data, $context);
```

### Example 3: Public vs Private API

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $apiToken,
    ) {}
}

// Public API: only safe fields
$publicOptions = new SerializationOptions(
    only: ['id', 'name'],
    wrap: 'user'
);

// Private/Admin API: include sensitive fields
$adminOptions = new SerializationOptions(
    except: ['password_hash'],  // Still hide hash
    wrap: 'user'
);

$publicResponse = $user->toArray($publicOptions);
$adminResponse = $user->toArray($adminOptions);
```

### Example 4: Performance Optimization

```php
// Shallow serialization for list endpoints
$listOptions = new SerializationOptions(
    maxDepth: 1,
    except: ['nested_data', 'computed_field']
);

$users = array_map(
    fn($user) => $user->toArray($listOptions),
    $allUsers
);

// Deep serialization for detail endpoints
$detailOptions = new SerializationOptions(
    maxDepth: 5,
    includeLazy: []  // Include all computed fields
);

$userDetail = $user->toArray($detailOptions);
```

---

## Best Practices

### 1. Use `only` for Security-Critical APIs

```php
// ✅ Explicit whitelist
$options = new SerializationOptions(only: ['id', 'name', 'email']);

// ❌ Blacklist might miss new fields
$options = new SerializationOptions(except: ['password']);
```

### 2. Set Reasonable Depth Limits

```php
// Prevent infinite recursion
$options = new SerializationOptions(maxDepth: 5);
```

### 3. Create Context Factories

```php
class ContextFactory
{
    public static function forApi(): Context
    {
        return new Context(
            validationEnabled: true,
            castMode: 'strict',
            namingStrategy: new SnakeCaseStrategy()
        );
    }
    
    public static function forInternal(): Context
    {
        return new Context(castMode: 'loose');
    }
}

$dto = UserDto::from($data, ContextFactory::forApi());
```

### 4. Reuse SerializationOptions

```php
class ApiOptions
{
    public static SerializationOptions $public;
    public static SerializationOptions $admin;
    
    public static function init(): void
    {
        self::$public = new SerializationOptions(
            only: ['id', 'name', 'email'],
            wrap: 'data'
        );
        
        self::$admin = new SerializationOptions(
            except: ['password_hash'],
            wrap: 'data'
        );
    }
}

ApiOptions::init();
$response = $user->toArray(ApiOptions::$public);
```

---

## Summary

- ✅ **SerializationOptions** - Control output (only, except, depth, lazy, wrap)
- ✅ **Context** - Control hydration & serialization globally
- ✅ **Cast Modes** - loose (default), strict, permissive
- ✅ **Naming Strategies** - Auto-convert property names
- ✅ **Security** - Use `only` for whitelisting sensitive APIs
- ✅ **Performance** - Limit depth, exclude expensive computed fields

---

**Next:** [Utility Methods](./utility-methods.md) | [Pipelines & Transformers](./pipelines-transformers.md)
