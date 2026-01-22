# 🎓 Basic Concepts

Understanding the core principles of **jooservices/dto**.

---

## 📦 What are DTOs?

**Data Transfer Object (DTO)** is a design pattern used to transfer data between different parts of an application.

### Key Characteristics:
- ✅ **Type-safe** - Guarantees data types
- ✅ **Immutable** - Cannot be changed after creation
- ✅ **Validated** - Ensures data integrity
- ✅ **Structured** - Clear, predictable format

### Why Use DTOs?

| Without DTOs | With DTOs |
|--------------|-----------|
| `$data['user']['email']` ❌ Prone to errors | `$user->email` ✅ Type-safe |
| No IDE autocomplete | Full IDE support |
| Runtime errors | Compile-time safety |
| Unclear structure | Self-documenting code |

---

## 🔄 DTO vs Data

This library provides **two base classes**:

### `Dto` - Immutable Objects

```php
use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}

$user = UserDto::from(['name' => 'John', 'email' => 'john@example.com']);
// $user->name = 'Jane'; // ❌ Error: Cannot modify readonly property
```

**Use when:**
- 🔒 Data should not change
- 📤 Transferring data between layers
- 🌐 API responses
- 📝 Configuration objects

### `Data` - Mutable Objects

```php
use JOOservices\Dto\Core\Data;

class UserData extends Data
{
    public string $name;
    public string $email;
}

$user = UserData::from(['name' => 'John', 'email' => 'john@example.com']);
$user->name = 'Jane'; // ✅ Allowed
```

**Use when:**
- ✏️ Data needs to be modified
- 🔄 Building objects incrementally
- 📝 Form data processing
- 🛠️ Internal data manipulation

---

## 🏗️ Creating Objects

### Method 1: from() - From Arrays

```php
$user = UserDto::from([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### Method 2: Direct Constructor

```php
$user = new UserDto(
    name: 'John Doe',
    email: 'john@example.com'
);
```

### Method 3: From JSON

```php
$json = '{"name":"John Doe","email":"john@example.com"}';
$user = UserDto::from(json_decode($json, true));
```

---

## 🔄 Type Casting

The library automatically converts types:

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly int $stock,
        public readonly bool $active,
        public readonly DateTime $createdAt,
    ) {}
}

$product = ProductDto::from([
    'name' => 'Laptop',
    'price' => '999.99',          // string → float
    'stock' => '50',              // string → int
    'active' => 1,                // int → bool
    'created_at' => '2024-01-20', // string → DateTime
]);
```

### Supported Type Casts:

| From | To | Example |
|------|----|---------| |
| `string` | `int` | `'123'` → `123` |
| `string` | `float` | `'99.99'` → `99.99` |
| `int` | `bool` | `1` → `true` |
| `string` | `DateTime` | `'2024-01-20'` → DateTime |
| `array` | Object | `['key' => 'value']` → NestedDto |
| `string` | `Enum` | `'ACTIVE'` → Status::ACTIVE |

---

## 🏗️ Nested Objects

DTOs can contain other DTOs:

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}
}

// Nested creation
$user = UserDto::from([
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York'
    ]
]);

// Access nested
echo $user->address->city; // New York
```

---

## 📋 Collections

Handle arrays of objects:

```php
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
```

---

## 🎨 Attributes

Customize behavior with PHP attributes:

### `#[MapFrom]` - Map Different Keys

```php
use JOOservices\Dto\Attributes\MapFrom;

class UserDto extends Dto
{
    public function __construct(
        #[MapFrom('full_name')]
        public readonly string $name,
    ) {}
}

$user = UserDto::from(['full_name' => 'John Doe']);
echo $user->name; // John Doe
```

### `#[Hidden]` - Hide from Output

```php
use JOOservices\Dto\Attributes\Hidden;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $email,
        
        #[Hidden]
        public readonly string $password,
    ) {}
}

$user = UserDto::from([
    'email' => 'john@example.com',
    'password' => 'secret'
]);

$array = $user->toArray();
// ['email' => 'john@example.com']
// Password is hidden
```

### `#[CastWith]` - Custom Casters

```php
use JOOservices\Dto\Attributes\CastWith;

class UserDto extends Dto
{
    public function __construct(
        #[CastWith(CustomCaster::class)]
        public readonly mixed $customField,
    ) {}
}
```

---

## 🔄 Serialization

### To Array

```php
$array = $user->toArray();
```

### To JSON

```php
$json = json_encode($user->toArray());
```

### Options

```php
use JOOservices\Dto\Core\SerializationOptions;

$array = $user->toArray(
    SerializationOptions::create()
        ->includeNulls(false)
        ->transformKeys('snake_case')
);
```

---

## 🎯 Naming Strategies

Convert between naming conventions:

### camelCase ↔ snake_case

```php
// Input: snake_case
$dto = UserDto::from([
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

// Property: camelCase
echo $dto->firstName;  // John
echo $dto->lastName;   // Doe

// Output: snake_case
$array = $dto->toArray();
// ['first_name' => 'John', 'last_name' => 'Doe']
```

---

## ⚡ Performance

### Metadata Caching

The library caches class metadata for performance:

```php
// First call: Slower (builds metadata)
$user1 = UserDto::from($data);

// Subsequent calls: Faster (uses cache)
$user2 = UserDto::from($data);
$user3 = UserDto::from($data);
```

### Production Optimization

- ✅ Metadata is cached automatically
- ✅ No runtime reflection after first use
- ✅ Minimal overhead

---

## 🔍 Comparison with Arrays

### Arrays (❌ Not Recommended)

```php
$user = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
];

// Problems:
echo $user['emial']; // Typo! No error until runtime
$user['age'] = 'thirty'; // Type changed! No error
if (isset($user['phone'])) // Check needed everywhere
```

### DTOs (✅ Recommended)

```php
$user = UserDto::from([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

// Benefits:
echo $user->email; // IDE autocomplete, type-safe
// $user->age = 'thirty'; // Compile error!
// No isset() checks needed
```

---

## 📚 Summary

| Concept | Key Points |
|---------|------------|
| **DTO** | Immutable, type-safe data containers |
| **Data** | Mutable data objects |
| **Type Casting** | Automatic type conversion |
| **Nested Objects** | DTOs can contain other DTOs |
| **Attributes** | Customize behavior |
| **Serialization** | Convert to/from arrays/JSON |
| **Performance** | Metadata caching for speed |

---

## 🚀 Next Steps

Now that you understand the basics:

1. 📖 [User Guide](../02-user-guide/creating-dtos.md) - Learn all features in detail
2. 💡 [Examples](../03-examples/) - See real-world usage
3. 🔧 [API Reference](../05-api-reference/) - Explore all methods

---

**Questions?** See the [FAQ](../02-user-guide/troubleshooting.md) or [ask on GitHub](https://github.com/jooservices/dto/discussions).
