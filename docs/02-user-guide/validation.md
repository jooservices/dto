# ✅ Validation

Add validation rules to your DTOs using PHP attributes.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Enabling Validation](#enabling-validation)
3. [Built-in Validators](#built-in-validators)
4. [Handling Validation Errors](#handling-validation-errors)
5. [Combining Validators](#combining-validators)
6. [Custom Messages](#custom-messages)
7. [Best Practices](#best-practices)

---

## Introduction

The validation system allows you to add validation rules to DTO properties using PHP attributes. Validation is **opt-in** and runs **before** type casting, ensuring raw input data is validated.

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\Email;

class UserDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $name,
        
        #[Required]
        #[Email]
        public readonly string $email,
    ) {}
}
```

---

## Enabling Validation

Validation is **disabled by default** for performance. Enable it via `Context`:

```php
use JOOservices\Dto\Core\Context;

// ❌ Validation disabled (default)
$user = UserDto::from(['name' => '', 'email' => 'invalid']);
// No error - invalid data is accepted

// ✅ Validation enabled
$context = new Context(validationEnabled: true);
$user = UserDto::from(['name' => 'John', 'email' => 'john@example.com'], $context);
// Validation runs and throws exception on failure
```

### Why Opt-In?

- **Performance**: Skip validation when not needed (e.g., trusted data sources)
- **Backward Compatibility**: Existing code continues to work
- **Explicit Intent**: Clear when validation is expected

---

## Built-in Validators

### #[Required]

Ensures the property has a non-null, non-empty value.

```php
use JOOservices\Dto\Attributes\Validation\Required;

class UserDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $name,
        
        #[Required(message: 'Email is mandatory')]
        public readonly string $email,
    ) {}
}
```

**Fails for:**
- `null`
- Empty string `''`
- Empty array `[]`

**Passes for:**
- Any non-empty value
- `0` (zero is a valid value)
- `false` (boolean false is a valid value)

### #[Email]

Validates that the value is a valid email address.

```php
use JOOservices\Dto\Attributes\Validation\Email;

class ContactDto extends Dto
{
    public function __construct(
        #[Email]
        public readonly string $email,
        
        #[Email(message: 'Invalid work email format')]
        public readonly ?string $workEmail = null,
    ) {}
}
```

**Fails for:**
- `'invalid-email'`
- `'user@'`
- `'@example.com'`
- Non-string values

**Passes for:**
- `'user@example.com'`
- `'john+newsletter@mail.example.com'`
- `null` (use `#[Required]` to require a value)
- Empty string `''` (use `#[Required]` to require non-empty)

### #[Url]

Validates that the value is a valid URL.

```php
use JOOservices\Dto\Attributes\Validation\Url;

class LinkDto extends Dto
{
    public function __construct(
        #[Url]
        public readonly string $website,
        
        #[Url(message: 'Please provide a valid website URL')]
        public readonly ?string $homepage = null,
    ) {}
}
```

**Fails for:**
- `'not-a-url'`
- `'example.com'` (missing protocol)
- Non-string values

**Passes for:**
- `'https://example.com'`
- `'http://localhost:8080/path'`
- `null` (use `#[Required]` to require a value)

### #[Min]

Validates that a numeric value is at least the specified minimum.

```php
use JOOservices\Dto\Attributes\Validation\Min;

class ProductDto extends Dto
{
    public function __construct(
        #[Min(0)]
        public readonly float $price,
        
        #[Min(1, message: 'Quantity must be at least 1')]
        public readonly int $quantity,
    ) {}
}
```

### #[Max]

Validates that a numeric value does not exceed the specified maximum.

```php
use JOOservices\Dto\Attributes\Validation\Max;

class OrderDto extends Dto
{
    public function __construct(
        #[Max(100)]
        public readonly int $quantity,
        
        #[Max(10000, message: 'Maximum order value is 10,000')]
        public readonly float $total,
    ) {}
}
```

### #[Between]

Validates that a numeric value is within a specified range (inclusive).

```php
use JOOservices\Dto\Attributes\Validation\Between;

class RatingDto extends Dto
{
    public function __construct(
        #[Between(1, 5)]
        public readonly int $stars,
        
        #[Between(0, 100, message: 'Percentage must be between 0 and 100')]
        public readonly float $percentage,
    ) {}
}
```

### #[Length]

Validates string length constraints (minimum and/or maximum).

```php
use JOOservices\Dto\Attributes\Validation\Length;

class UserDto extends Dto
{
    public function __construct(
        #[Length(min: 2, max: 50)]
        public readonly string $username,
        
        #[Length(min: 8, message: 'Password must be at least 8 characters')]
        public readonly string $password,
        
        #[Length(max: 500)]
        public readonly ?string $bio = null,
    ) {}
}
```

### #[Regex]

Validates that the value matches a regular expression pattern.

```php
use JOOservices\Dto\Attributes\Validation\Regex;

class PhoneDto extends Dto
{
    public function __construct(
        #[Regex('/^\+?[1-9]\d{1,14}$/')]
        public readonly string $phoneNumber,
        
        #[Regex('/^[A-Z]{2}\d{6}$/', message: 'Invalid passport format')]
        public readonly string $passportNumber,
    ) {}
}
```

### #[RequiredIf]

Makes a field required only when another field has a specific value.

```php
use JOOservices\Dto\Attributes\Validation\RequiredIf;

class PaymentDto extends Dto
{
    public function __construct(
        public readonly string $paymentMethod,
        
        #[RequiredIf('paymentMethod', 'credit_card')]
        public readonly ?string $cardNumber = null,
        
        #[RequiredIf('paymentMethod', 'credit_card')]
        public readonly ?string $cvv = null,
        
        #[RequiredIf('subscribe', true, message: 'Email required for newsletter')]
        public readonly ?string $email = null,
        
        public readonly bool $subscribe = false,
    ) {}
}
```

### #[Valid]

Triggers validation of nested DTOs (nested validation happens during hydration).

```php
use JOOservices\Dto\Attributes\Validation\Valid;

class OrderDto extends Dto
{
    public function __construct(
        #[Valid]
        public readonly AddressDto $shippingAddress,
        
        #[Valid(eachItem: true)]
        /** @var OrderItemDto[] */
        public readonly array $items,
    ) {}
}
```

**Note:** Nested DTOs are automatically validated during hydration when `validationEnabled: true` is set in the Context.

---

## Handling Validation Errors

When validation fails, a `HydrationException` is thrown containing the validation errors:

```php
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;

$context = new Context(validationEnabled: true);

try {
    $user = UserDto::from([
        'name' => '',
        'email' => 'invalid-email',
    ], $context);
} catch (HydrationException $e) {
    // Get all nested errors
    foreach ($e->getErrors() as $error) {
        if ($error instanceof ValidationException) {
            foreach ($error->getViolations() as $violation) {
                echo $violation->getPropertyName() . ': ' . $violation->getMessage() . "\n";
            }
        }
    }
    
    // Or use the full message
    echo $e->getFullMessage();
}
```

### RuleViolation Properties

Each violation contains:

| Property | Description |
|----------|-------------|
| `getPropertyName()` | Name of the property that failed |
| `getRuleName()` | Name of the validation rule (e.g., 'required', 'email') |
| `getMessage()` | Human-readable error message |
| `getInvalidValue()` | The value that failed validation |

---

## Combining Validators

You can use multiple validators on the same property:

```php
class UserDto extends Dto
{
    public function __construct(
        #[Required]
        #[Email]
        public readonly string $email,
    ) {}
}
```

Validators run in priority order:
1. `#[Required]` runs first (higher priority)
2. `#[Email]` runs after

If `#[Required]` fails, `#[Email]` still runs and both errors are collected.

---

## Custom Messages

Override the default error message with a custom one:

```php
class RegistrationDto extends Dto
{
    public function __construct(
        #[Required(message: 'Please enter your name')]
        public readonly string $name,
        
        #[Required(message: 'Email is required for account creation')]
        #[Email(message: 'Please provide a valid email address')]
        public readonly string $email,
    ) {}
}
```

### Default Messages

| Validator | Default Message |
|-----------|-----------------|
| `#[Required]` | "This field is required" |
| `#[Email]` | "The value must be a valid email address" |

---

## Best Practices

### ✅ DO: Combine Required with Type Validators

```php
// ✅ Good: Required first, then type validation
#[Required]
#[Email]
public readonly string $email;
```

```php
// ⚠️ Works but incomplete: Email allows null/empty
#[Email]
public readonly ?string $email;  // null is valid
```

### ✅ DO: Use Meaningful Custom Messages

```php
// ✅ Good: Context-specific message
#[Required(message: 'Email is required for newsletter subscription')]
public readonly string $email;
```

```php
// ❌ Bad: Generic message
#[Required(message: 'Required')]
public readonly string $email;
```

### ✅ DO: Enable Validation at API Boundaries

```php
// ✅ Good: Validate user input
$context = new Context(validationEnabled: true);
$request = CreateUserDto::from($requestBody, $context);
```

```php
// ✅ Good: Skip validation for trusted data
$response = UserResponseDto::from($databaseRecord);  // No validation needed
```

### ❌ DON'T: Rely on Validation for Type Safety

```php
// ❌ Bad: Validation alone doesn't guarantee type
#[Email]
public readonly mixed $email;  // Could still be an array
```

```php
// ✅ Good: Type hint + validation
#[Email]
public readonly string $email;  // Guaranteed to be string + valid email
```

---

## Adding Custom Validators

You can create custom validators by implementing `ValidatorInterface`. See [Custom Validators](../06-advanced/custom-validators.md) for details.

---

## Summary

| Feature | Description |
|---------|-------------|
| Opt-in via Context | `new Context(validationEnabled: true)` |
| Validates raw input | Before type casting |
| Collects all errors | Shows all validation failures at once |
| Extensible | Add custom validators |

---

## Next Steps

- 🔄 [Type Casting](./type-casting.md) - Automatic type conversion
- 🏗️ [Nested Objects](./nested-objects.md) - Complex structures
- 📚 [Best Practices](./best-practices.md) - Write better DTOs

---

**Questions?** See the [Troubleshooting Guide](./troubleshooting.md) or [ask on GitHub](https://github.com/jooservices/dto/discussions).
