# ⚡ Pipelines & Transformers

Complete guide to data transformation in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Pipeline System (Input)](#pipeline-system)
3. [Transformer System (Output)](#transformer-system)
4. [Global vs Property-Level](#global-vs-property-level)
5. [Real-World Examples](#real-world-examples)
6. [Best Practices](#best-practices)

---

## Introduction

Two complementary systems for transforming data:

| System | Stage | Purpose | Example |
|--------|-------|---------|---------|
| **Pipelines** | Hydration (Input) | Clean, normalize, validate incoming data | Trim whitespace, lowercase emails |
| **Transformers** | Serialization (Output) | Format data for output | Format dates, convert enums |

```
Input Data
    ↓
[Pipeline Steps]  ← Applied during hydration
    ↓
DTO Instance
    ↓
[Transformers]    ← Applied during serialization
    ↓
Output Data
```

---

## Pipeline System

**Transform input data BEFORE it becomes part of your DTO.**

### Built-in Pipeline Steps

| Step | Description | Example |
|------|-------------|---------|
| `TrimStrings` | Remove leading/trailing whitespace | `"  hello  "` → `"hello"` |
| `Lowercase` | Convert to lowercase | `"HELLO"` → `"hello"` |
| `Uppercase` | Convert to uppercase | `"hello"` → `"HELLO"` |
| `StripTags` | Remove HTML tags | `"<b>text</b>"` → `"text"` |

### Using #[Pipeline] Attribute

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Hydration\Pipeline\TrimStrings;
use JOOservices\Dto\Hydration\Pipeline\Lowercase;

class UserDto extends Dto
{
    public function __construct(
        #[Pipeline([TrimStrings::class, Lowercase::class])]
        public readonly string $email,
        
        #[Pipeline([TrimStrings::class])]
        public readonly string $name,
    ) {}
}

// Input with messy data
$user = UserDto::from([
    'email' => '  JOHN@EXAMPLE.COM  ',  // Trimmed + lowercased
    'name' => '  John Doe  ',            // Only trimmed
]);

echo $user->email;  // "john@example.com"
echo $user->name;   // "John Doe"
```

### Example: Email Normalization

```php
use JOOservices\Dto\Hydration\Pipeline\{TrimStrings, Lowercase};

class ContactDto extends Dto
{
    public function __construct(
        #[Pipeline([TrimStrings::class, Lowercase::class])]
        public readonly string $email,
    ) {}
}

$contact = ContactDto::from(['email' => '  USER@EXAMPLE.COM  ']);
// Result: "user@example.com"
```

### Example: Username Sanitization

```php
use JOOservices\Dto\Hydration\Pipeline\{TrimStrings, StripTags};

class RegistrationDto extends Dto
{
    public function __construct(
        #[Pipeline([StripTags::class, TrimStrings::class])]
        public readonly string $username,
    ) {}
}

$user = RegistrationDto::from([
    'username' => '  <script>alert("xss")</script>JohnDoe  '
]);
// Result: "JohnDoe" (HTML stripped, whitespace trimmed)
```

### Creating Custom Pipeline Steps

Implement `PipelineStepInterface`:

```php
use JOOservices\Dto\Hydration\PipelineStepInterface;

class RemoveSpecialChars implements PipelineStepInterface
{
    public function process(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }
        
        // Remove everything except letters, numbers, spaces
        return preg_replace('/[^a-zA-Z0-9\s]/', '', $value);
    }
}

// Use it
class ProductDto extends Dto
{
    public function __construct(
        #[Pipeline([RemoveSpecialChars::class, TrimStrings::class])]
        public readonly string $sku,
    ) {}
}

$product = ProductDto::from(['sku' => ' ABC-123!@# ']);
// Result: "ABC123"
```

### Custom Step with Configuration

```php
class Truncate implements PipelineStepInterface
{
    public function __construct(
        private readonly int $maxLength = 50,
        private readonly string $suffix = '...'
    ) {}
    
    public function process(mixed $value): mixed
    {
        if (!is_string($value) || strlen($value) <= $this->maxLength) {
            return $value;
        }
        
        return substr($value, 0, $this->maxLength) . $this->suffix;
    }
}

// Use with configuration
class PostDto extends Dto
{
    public function __construct(
        #[Pipeline([[Truncate::class, ['maxLength' => 100, 'suffix' => '...']]])]
        public readonly string $excerpt,
    ) {}
}
```

### Global Pipeline via Context

Apply transformations to ALL properties:

```php
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Hydration\Pipeline\TrimStrings;

$context = new Context(
    globalPipeline: [TrimStrings::class]
);

$dto = UserDto::from([
    'name' => '  John  ',
    'email' => '  john@example.com  '
], $context);
// Both name and email are automatically trimmed
```

---

## Transformer System

**Format data DURING serialization for output.**

### Built-in Transformers

| Transformer | Input | Output |
|-------------|-------|--------|
| `DateTimeTransformer` | `DateTime` object | Formatted string |
| `EnumTransformer` | `Enum` instance | Enum value (string/int) |

### Using #[TransformWith] Attribute

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;

class EventDto extends Dto
{
    public function __construct(
        public readonly string $title,
        
        #[TransformWith(DateTimeTransformer::class, ['format' => 'Y-m-d H:i:s'])]
        public readonly \DateTimeImmutable $startDate,
    ) {}
}

$event = new EventDto(
    title: 'Conference',
    startDate: new \DateTimeImmutable('2024-06-15 09:00:00')
);

$array = $event->toArray();
print_r($array);
```

**Output:**
```php
[
    'title' => 'Conference',
    'start_date' => '2024-06-15 09:00:00',  // Formatted as string
]
```

### Example: DateTime Formatting

```php
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;

class BlogPostDto extends Dto
{
    public function __construct(
        public readonly string $title,
        
        // ISO 8601 format for APIs
        #[TransformWith(DateTimeTransformer::class, ['format' => 'c'])]
        public readonly \DateTimeImmutable $publishedAt,
        
        // Human-readable format
        #[TransformWith(DateTimeTransformer::class, ['format' => 'F j, Y g:i A'])]
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
}

$post = new BlogPostDto(
    title: 'My Post',
    publishedAt: new \DateTimeImmutable('2024-01-20 14:30:00'),
    updatedAt: new \DateTimeImmutable('2024-01-21 16:45:00')
);

$json = $post->toJson();
// publishedAt: "2024-01-20T14:30:00+00:00"
// updatedAt: "January 21, 2024 4:45 PM"
```

### Example: Enum Transformation

```php
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
}

class OrderDto extends Dto
{
    public function __construct(
        public readonly int $id,
        
        #[TransformWith(EnumTransformer::class)]
        public readonly OrderStatus $status,
    ) {}
}

$order = new OrderDto(id: 123, status: OrderStatus::PROCESSING);

$array = $order->toArray();
// status: "processing" (string value, not enum object)
```

### Creating Custom Transformers

Implement `TransformerInterface`:

```php
use JOOservices\Dto\Normalization\TransformerInterface;

class MoneyTransformer implements TransformerInterface
{
    public function transform(mixed $value, array $options = []): string
    {
        if (!$value instanceof Money) {
            return $value;
        }
        
        $currency = $options['currency'] ?? 'USD';
        $symbol = $this->getCurrencySymbol($currency);
        
        return $symbol . number_format($value->amount / 100, 2);
    }
    
    private function getCurrencySymbol(string $currency): string
    {
        return match($currency) {
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency . ' '
        };
    }
}

// Use it
class InvoiceDto extends Dto
{
    public function __construct(
        #[TransformWith(MoneyTransformer::class, ['currency' => 'USD'])]
        public readonly Money $total,
    ) {}
}

$invoice = new InvoiceDto(total: new Money(amount: 9999, currency: 'USD'));
$array = $invoice->toArray();
// total: "$99.99"
```

### Custom Transformer: URL Generation

```php
class UrlTransformer implements TransformerInterface
{
    public function transform(mixed $value, array $options = []): string
    {
        $baseUrl = $options['base_url'] ?? 'https://example.com';
        $path = $options['path'] ?? '';
        
        return rtrim($baseUrl, '/') . '/' . ltrim($path . '/' . $value, '/');
    }
}

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        
        #[TransformWith(UrlTransformer::class, ['base_url' => 'https://api.example.com', 'path' => '/users'])]
        public readonly int $profileId,
    ) {}
}

$user = new UserDto(id: 1, profileId: 123);
$array = $user->toArray();
// profileId: "https://api.example.com/users/123"
```

---

## Global vs Property-Level

### Property-Level (Specific)

Applied to individual properties:

```php
class UserDto extends Dto
{
    public function __construct(
        #[Pipeline([TrimStrings::class])]
        public readonly string $name,
        
        #[Pipeline([TrimStrings::class, Lowercase::class])]
        public readonly string $email,
    ) {}
}
```

### Global (All Properties)

Applied to ALL properties via Context:

```php
$context = new Context(
    globalPipeline: [TrimStrings::class, Lowercase::class]
);

$dto = UserDto::from($data, $context);
// Both name and email trimmed + lowercased
```

### Combining Both

Global pipeline runs FIRST, then property-level:

```php
// Global: TrimStrings
$context = new Context(globalPipeline: [TrimStrings::class]);

class UserDto extends Dto
{
    public function __construct(
        // Additional Lowercase for email only
        #[Pipeline([Lowercase::class])]
        public readonly string $email,
        
        public readonly string $name,  // Only global TrimStrings
    ) {}
}

$user = UserDto::from([
    'email' => '  JOHN@EXAMPLE.COM  ',
    'name' => '  John Doe  '
], $context);

// email: trimmed (global) + lowercased (property) = "john@example.com"
// name: trimmed (global) only = "John Doe"
```

---

## Real-World Examples

### Example 1: API Input Sanitization

```php
use JOOservices\Dto\Hydration\Pipeline\{TrimStrings, StripTags, Lowercase};

class ApiRequestDto extends Dto
{
    public function __construct(
        #[Pipeline([StripTags::class, TrimStrings::class])]
        public readonly string $query,
        
        #[Pipeline([TrimStrings::class, Lowercase::class])]
        public readonly string $sortBy,
    ) {}
}

// Protect against XSS and normalize input
$request = ApiRequestDto::from([
    'query' => '<script>alert("xss")</script>  search term  ',
    'sort_by' => '  CREATED_AT  '
]);

// query: "search term"
// sortBy: "created_at"
```

### Example 2: User Registration

```php
class UserRegistrationDto extends Dto
{
    public function __construct(
        #[Pipeline([StripTags::class, TrimStrings::class])]
        public readonly string $username,
        
        #[Pipeline([TrimStrings::class, Lowercase::class])]
        public readonly string $email,
        
        public readonly string $password,  // Don't transform passwords!
    ) {}
}
```

### Example 3: API Response Formatting

```php
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;

class ApiUserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        
        #[TransformWith(DateTimeTransformer::class, ['format' => 'c'])]
        public readonly \DateTimeImmutable $createdAt,
        
        #[TransformWith(DateTimeTransformer::class, ['format' => 'c'])]
        public readonly ?\DateTimeImmutable $lastLoginAt,
    ) {}
}

// All dates formatted as ISO 8601 for consistent API responses
```

### Example 4: CSV Import Pipeline

```php
class CsvRowDto extends Dto
{
    public function __construct(
        #[Pipeline([TrimStrings::class, Uppercase::class])]
        public readonly string $countryCode,  // "  us  " → "US"
        
        #[Pipeline([TrimStrings::class])]
        public readonly string $name,
        
        #[Pipeline([TrimStrings::class, Lowercase::class])]
        public readonly string $email,
    ) {}
}
```

### Example 5: Multi-Format Date Output

```php
class EventDto extends Dto
{
    public function __construct(
        public readonly string $name,
        
        // ISO format for APIs
        #[TransformWith(DateTimeTransformer::class, ['format' => 'Y-m-d\TH:i:sP'])]
        public readonly \DateTimeImmutable $startDate,
        
        // Human-readable
        #[TransformWith(DateTimeTransformer::class, ['format' => 'F j, Y \a\t g:i A'])]
        public readonly \DateTimeImmutable $displayDate,
    ) {}
}

$event = new EventDto(
    name: 'Conference',
    startDate: new \DateTimeImmutable('2024-06-15 09:00:00'),
    displayDate: new \DateTimeImmutable('2024-06-15 09:00:00')
);

$json = $event->toJson();
// startDate: "2024-06-15T09:00:00+00:00"
// displayDate: "June 15, 2024 at 9:00 AM"
```

---

## Best Practices

### 1. Keep Pipeline Steps Simple

✅ **DO:**
```php
#[Pipeline([TrimStrings::class, Lowercase::class])]
```

❌ **DON'T:**
```php
// Too complex for a pipeline step
class ValidateAndHashPassword implements PipelineStepInterface {
    // This should be in afterHydration() hook instead
}
```

### 2. Don't Transform Passwords

```php
class UserDto extends Dto
{
    public function __construct(
        #[Pipeline([TrimStrings::class])]
        public readonly string $username,
        
        // NO pipeline on password!
        public readonly string $password,
    ) {}
}
```

### 3. Use Transformers for Output Formatting

```php
// ✅ Good: Format on output
#[TransformWith(DateTimeTransformer::class, ['format' => 'Y-m-d'])]
public readonly \DateTimeImmutable $date;
```

### 4. Combine for Complete Data Flow

```php
class ProductDto extends Dto
{
    public function __construct(
        // Input: trim and uppercase SKU
        #[Pipeline([TrimStrings::class, Uppercase::class])]
        
        // Output: add prefix for display
        #[TransformWith(SkuFormatter::class)]
        public readonly string $sku,
    ) {}
}
```

---

## Performance Tips

### 1. Order Steps Efficiently

```php
// ✅ Efficient: Trim first (removes characters)
#[Pipeline([TrimStrings::class, Lowercase::class])]

// ❌ Less efficient: Process more characters
#[Pipeline([Lowercase::class, TrimStrings::class])]
```

### 2. Use Global Pipelines Sparingly

```php
// ❌ Heavy global pipeline affects ALL properties
$context = new Context(globalPipeline: [
    TrimStrings::class,
    StripTags::class,
    Lowercase::class,
    CustomExpensiveStep::class
]);

// ✅ Apply only where needed
class UserDto extends Dto
{
    #[Pipeline([TrimStrings::class, Lowercase::class])]
    public readonly string $email;
    
    public readonly int $id;  // No pipeline needed
}
```

### 3. Cache Transformer Results

```php
class ExpensiveTransformer implements TransformerInterface
{
    private array $cache = [];
    
    public function transform(mixed $value, array $options = []): mixed
    {
        $key = serialize([$value, $options]);
        
        if (!isset($this->cache[$key])) {
            $this->cache[$key] = $this->expensiveOperation($value, $options);
        }
        
        return $this->cache[$key];
    }
}
```

---

## Summary

- ✅ **Pipelines** - Transform input during hydration (cleaning, normalization)
- ✅ **Transformers** - Format output during serialization (display formatting)
- ✅ **Built-in steps** - TrimStrings, Lowercase, Uppercase, StripTags
- ✅ **Built-in transformers** - DateTimeTransformer, EnumTransformer
- ✅ **Custom steps** - Implement PipelineStepInterface
- ✅ **Custom transformers** - Implement TransformerInterface
- ✅ **Global pipelines** - Apply to all properties via Context

---

**Next:** [Utility Methods](./utility-methods.md) | [Lifecycle Hooks](./lifecycle-hooks.md)
