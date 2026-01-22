# 🔄 Type Casting

Complete guide to automatic type casting in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [How Type Casting Works](#how-type-casting-works)
3. [Built-in Type Casters](#built-in-type-casters)
4. [Custom Casters](#custom-casters)
5. [Cast Modes](#cast-modes)
6. [Best Practices](#best-practices)
7. [Troubleshooting](#troubleshooting)

---

## Introduction

The library automatically converts input data to match your DTO property types during hydration. This ensures type safety and eliminates manual casting logic.

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $age,           // Automatically cast to int
        public readonly DateTimeImmutable $createdAt,  // Cast to DateTime
        public readonly Status $status,     // Cast to enum
    ) {}
}

$user = UserDto::from([
    'age' => '25',                    // string → int
    'created_at' => '2024-01-20',     // string → DateTimeImmutable
    'status' => 'active',             // string → Status enum
]);
```

---

## How Type Casting Works

### Automatic Detection

The library analyzes your property type hints and automatically applies the appropriate caster:

| Property Type | Caster Used | Handles |
|---------------|-------------|---------|
| `int`, `float`, `string`, `bool` | ScalarCaster | Primitive types |
| `DateTime`, `DateTimeImmutable` | DateTimeCaster | Date/time objects |
| `BackedEnum`, `UnitEnum` | EnumCaster | Enumerations |
| `UserDto[]`, `array<UserDto>` | ArrayOfCaster | Typed arrays |

### Type Resolution

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly int $id,              // ScalarCaster
        public readonly string $name,         // ScalarCaster
        public readonly float $price,         // ScalarCaster
        public readonly DateTimeImmutable $created,  // DateTimeCaster
        public readonly Status $status,       // EnumCaster
        /** @var TagDto[] */
        public readonly array $tags,          // ArrayOfCaster
    ) {}
}
```

---

## Built-in Type Casters

### 1. ScalarCaster

Handles `int`, `float`, `string`, `bool` with intelligent conversion.

#### Integer Casting

```php
class StatsDto extends Dto
{
    public function __construct(
        public readonly int $count,
    ) {}
}

// All these work:
StatsDto::from(['count' => 42]);          // int → int
StatsDto::from(['count' => '42']);        // string → int
StatsDto::from(['count' => 42.7]);        // float → int (42)
StatsDto::from(['count' => true]);        // bool → int (1)
```

#### Float Casting

```php
class PriceDto extends Dto
{
    public function __construct(
        public readonly float $amount,
    ) {}
}

// All these work:
PriceDto::from(['amount' => 19.99]);      // float → float
PriceDto::from(['amount' => '19.99']);    // string → float
PriceDto::from(['amount' => 20]);         // int → float
```

#### String Casting

```php
class MessageDto extends Dto
{
    public function __construct(
        public readonly string $text,
    ) {}
}

// All these work:
MessageDto::from(['text' => 'Hello']);    // string → string
MessageDto::from(['text' => 42]);         // int → string ("42")
MessageDto::from(['text' => true]);       // bool → string ("1")
MessageDto::from(['text' => 3.14]);       // float → string ("3.14")
```

#### Boolean Casting

Smart boolean conversion handles various formats:

```php
class FeatureDto extends Dto
{
    public function __construct(
        public readonly bool $enabled,
    ) {}
}

// TRUE values:
FeatureDto::from(['enabled' => true]);
FeatureDto::from(['enabled' => 1]);
FeatureDto::from(['enabled' => '1']);
FeatureDto::from(['enabled' => 'true']);
FeatureDto::from(['enabled' => 'yes']);
FeatureDto::from(['enabled' => 'on']);

// FALSE values:
FeatureDto::from(['enabled' => false]);
FeatureDto::from(['enabled' => 0]);
FeatureDto::from(['enabled' => '0']);
FeatureDto::from(['enabled' => 'false']);
FeatureDto::from(['enabled' => 'no']);
FeatureDto::from(['enabled' => 'off']);
FeatureDto::from(['enabled' => '']);
```

---

### 2. DateTimeCaster

Converts strings, timestamps, and DateTime objects to `DateTime` or `DateTimeImmutable`.

```php
class EventDto extends Dto
{
    public function __construct(
        public readonly DateTimeImmutable $startDate,
        public readonly DateTime $endDate,
    ) {}
}

// From ISO 8601 string:
EventDto::from([
    'start_date' => '2024-01-20T10:30:00+00:00',
    'end_date' => '2024-01-20T12:00:00+00:00',
]);

// From human-readable string:
EventDto::from([
    'start_date' => '2024-01-20 10:30:00',
    'end_date' => 'tomorrow',
]);

// From Unix timestamp:
EventDto::from([
    'start_date' => 1705747800,
    'end_date' => 1705755000,
]);

// From DateTime object:
EventDto::from([
    'start_date' => new DateTime('now'),
    'end_date' => new DateTimeImmutable('+2 hours'),
]);
```

---

### 3. EnumCaster

Converts strings/integers to PHP 8.1+ enums.

#### Backed Enums (String/Int)

```php
enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}

class UserDto extends Dto
{
    public function __construct(
        public readonly Status $status,
    ) {}
}

// From enum value:
UserDto::from(['status' => 'active']);        // ✅
UserDto::from(['status' => Status::ACTIVE]);  // ✅
```

#### Unit Enums (No Backing Value)

```php
enum Priority
{
    case LOW;
    case MEDIUM;
    case HIGH;
}

class TaskDto extends Dto
{
    public function __construct(
        public readonly Priority $priority,
    ) {}
}

// From enum name:
TaskDto::from(['priority' => 'LOW']);           // ✅
TaskDto::from(['priority' => Priority::HIGH]);  // ✅
```

---

### 4. ArrayOfCaster

Automatically casts arrays of DTOs.

```php
class TagDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {}
}

class PostDto extends Dto
{
    public function __construct(
        public readonly string $title,
        /** @var TagDto[] */
        public readonly array $tags,
    ) {}
}

$post = PostDto::from([
    'title' => 'My Post',
    'tags' => [
        ['name' => 'PHP'],
        ['name' => 'DTO'],
        ['name' => 'Tutorial'],
    ],
]);

// $post->tags is now an array of TagDto objects
foreach ($post->tags as $tag) {
    echo $tag->name; // TagDto instance
}
```

---

## Custom Casters

Create custom casters for specialized type conversions.

### Step 1: Create the Caster

```php
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

class MoneyCaster implements CasterInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->type->name === Money::class
            && (is_array($value) || is_int($value) || is_float($value));
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): Money
    {
        if ($value instanceof Money) {
            return $value;
        }

        if (is_numeric($value)) {
            return new Money((int)($value * 100), 'USD');
        }

        if (is_array($value)) {
            return new Money(
                $value['amount'] ?? 0,
                $value['currency'] ?? 'USD'
            );
        }

        throw new \Exception("Cannot cast to Money");
    }
}
```

### Step 2: Use with Attribute

```php
use JOOservices\Dto\Attributes\CastWith;

class OrderDto extends Dto
{
    public function __construct(
        public readonly int $id,
        #[CastWith(MoneyCaster::class)]
        public readonly Money $total,
    ) {}
}

// Usage:
$order = OrderDto::from([
    'id' => 1,
    'total' => 19.99,  // Converted to Money object
]);

$order = OrderDto::from([
    'id' => 1,
    'total' => ['amount' => 1999, 'currency' => 'USD'],
]);
```

### Advanced: Configurable Casters

```php
class UrlCaster implements CasterInterface
{
    public function __construct(
        private readonly bool $requireHttps = false,
    ) {}

    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return is_string($value) && filter_var($value, FILTER_VALIDATE_URL);
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): string
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            throw new \Exception("Invalid URL: {$value}");
        }

        if ($this->requireHttps && !str_starts_with($value, 'https://')) {
            throw new \Exception("HTTPS required");
        }

        return $value;
    }
}

// Usage:
class WebsiteDto extends Dto
{
    public function __construct(
        #[CastWith(UrlCaster::class, ['requireHttps' => true])]
        public readonly string $url,
    ) {}
}
```

---

## Cast Modes

Control casting behavior with `Context`:

### Strict Mode (Default)

Throws exceptions on type mismatches:

```php
$context = new Context(castMode: CastMode::STRICT);
$dto = UserDto::from(['age' => 'invalid'], $context);
// ❌ Throws CastException
```

### Loose Mode

Attempts conversion, falls back to null/default:

```php
$context = new Context(castMode: CastMode::LOOSE);
$dto = UserDto::from(['age' => 'invalid'], $context);
// ✅ Sets age to 0 or null (depending on nullable)
```

---

## Best Practices

### 1. Use Type Hints

✅ **DO:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $age,              // Type hint present
        public readonly string $name,          // Type hint present
    ) {}
}
```

❌ **DON'T:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly $age,                  // Missing type hint
        public readonly $name,                 // Missing type hint
    ) {}
}
```

### 2. Document Array Types

✅ **DO:**
```php
class PostDto extends Dto
{
    public function __construct(
        /** @var TagDto[] */
        public readonly array $tags,          // Documented
    ) {}
}
```

❌ **DON'T:**
```php
class PostDto extends Dto
{
    public function __construct(
        public readonly array $tags,          // No type info
    ) {}
}
```

### 3. Custom Casters for Complex Types

✅ **DO:** Create a caster for non-standard types
```php
#[CastWith(MoneyCaster::class)]
public readonly Money $price;
```

❌ **DON'T:** Try to handle complex casting manually
```php
public readonly Money $price;  // Won't work without caster
```

---

## Troubleshooting

### Issue: Type Not Casting

**Problem:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly $age,  // No type hint!
    ) {}
}
```

**Solution:** Add type hint
```php
public readonly int $age,
```

---

### Issue: Array of DTOs Not Working

**Problem:**
```php
public readonly array $tags;  // No documentation
```

**Solution:** Add PHPDoc
```php
/** @var TagDto[] */
public readonly array $tags;
```

---

### Issue: Custom Type Not Casting

**Problem:**
```php
public readonly Money $total;  // No caster registered
```

**Solution:** Use #[CastWith] attribute
```php
#[CastWith(MoneyCaster::class)]
public readonly Money $total;
```

---

### Issue: DateTime Format Not Recognized

**Problem:**
```php
EventDto::from(['date' => '20-01-2024']);  // Wrong format
```

**Solution:** Use ISO 8601 or create custom DateTime caster with your format:
```php
class CustomDateTimeCaster extends DateTimeCaster
{
    public function __construct()
    {
        parent::__construct(format: 'd-m-Y');
    }
}

#[CastWith(CustomDateTimeCaster::class)]
public readonly DateTimeImmutable $date;
```

---

## Summary

- ✅ **Automatic casting** for scalar types, DateTime, Enums, and arrays
- ✅ **Smart conversions** (e.g., "true" → true, "123" → 123)
- ✅ **Custom casters** via `CasterInterface` and `#[CastWith]`
- ✅ **Cast modes** (strict/loose) for different scenarios
- ✅ **Type-safe** with proper type hints and documentation

---

**Next:** [Nested Objects](./nested-objects.md) | [Arrays & Collections](./arrays-collections.md)
