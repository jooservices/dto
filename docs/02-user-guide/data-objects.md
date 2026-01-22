# ✏️ Data Objects (Mutable)

Complete guide to using mutable Data objects in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [DTO vs Data](#dto-vs-data)
3. [Creating Data Objects](#creating-data-objects)
4. [Mutating Data](#mutating-data)
5. [When to Use Data](#when-to-use-data)
6. [Best Practices](#best-practices)
7. [Common Patterns](#common-patterns)
8. [Troubleshooting](#troubleshooting)

---

## Introduction

While `Dto` objects are **immutable** (readonly properties), `Data` objects are **mutable** and allow property updates. Both classes share the same hydration, casting, and validation features.

```php
use JOOservices\Dto\Core\Data;

class UserData extends Data
{
    public function __construct(
        public string $name,      // NOT readonly - can be changed
        public string $email,
        public int $age,
    ) {}
}

$user = UserData::from(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

// Mutate properties:
$user->name = 'Jane';
$user->age = 31;

echo $user->name;  // "Jane"
```

---

## DTO vs Data

### Dto (Immutable)

```php
use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,      // readonly = immutable
        public readonly string $email,
        public readonly int $age,
    ) {}
}

$user = UserDto::from(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

// ❌ Cannot modify:
$user->name = 'Jane';  // Error: Cannot modify readonly property
```

### Data (Mutable)

```php
use JOOservices\Dto\Core\Data;

class UserData extends Data
{
    public function __construct(
        public string $name,               // No readonly = mutable
        public string $email,
        public int $age,
    ) {}
}

$user = UserData::from(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

// ✅ Can modify:
$user->name = 'Jane';
$user->age = 31;
```

### Comparison Table

| Feature | Dto (Immutable) | Data (Mutable) |
|---------|----------------|----------------|
| **Properties** | `readonly` | Regular (no `readonly`) |
| **Modification** | ❌ Cannot change | ✅ Can change |
| **Use Case** | Value objects, API responses | Forms, builders, stateful objects |
| **Thread Safety** | ✅ Safe | ⚠️ Not safe |
| **Hydration** | ✅ Full support | ✅ Full support |
| **Casting** | ✅ Full support | ✅ Full support |
| **Validation** | ✅ Full support | ✅ Full support |
| **Methods** | `update()`, `set()` | ✅ Available | ✅ Available |

---

## Creating Data Objects

### Basic Data Object

```php
use JOOservices\Dto\Core\Data;

class ContactData extends Data
{
    public function __construct(
        public string $name,
        public string $email,
        public ?string $phone = null,
    ) {}
}

// Create from array:
$contact = ContactData::from([
    'name' => 'Alice',
    'email' => 'alice@example.com',
]);

// Modify:
$contact->name = 'Alice Smith';
$contact->phone = '+1-555-1234';
```

### With Type Casting

```php
use JOOservices\Dto\Core\Data;

class SettingsData extends Data
{
    public function __construct(
        public bool $enabled,
        public int $timeout,
        public DateTimeImmutable $lastUpdated,
    ) {}
}

$settings = SettingsData::from([
    'enabled' => 'true',           // Cast to bool
    'timeout' => '30',             // Cast to int
    'last_updated' => '2024-01-20', // Cast to DateTimeImmutable
]);

// Modify:
$settings->enabled = false;
$settings->timeout = 60;
```

### With Validation

```php
use JOOservices\Dto\Core\Data;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\Email;

class RegistrationData extends Data
{
    public function __construct(
        #[Required]
        public string $username,
        
        #[Required, Email]
        public string $email,
        
        #[Required]
        public string $password,
    ) {}
}

$data = RegistrationData::from([
    'username' => 'john_doe',
    'email' => 'john@example.com',
    'password' => 'secret123',
], new Context(validationEnabled: true));

// Modify after validation:
$data->password = 'newSecret456';
```

---

## Mutating Data

### Direct Property Assignment

```php
$user = UserData::from(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

// Direct modification:
$user->name = 'Jane';
$user->age = 31;
$user->email = 'jane@example.com';
```

### Using update() Method

Batch update multiple properties:

```php
$user = UserData::from(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

// Update multiple properties at once:
$user->update([
    'name' => 'Jane',
    'age' => 31,
]);

echo $user->name;  // "Jane"
echo $user->age;   // 31
```

### Using set() Method

Update a single property:

```php
$user = UserData::from(['name' => 'John', 'email' => 'john@example.com', 'age' => 30]);

// Set single property:
$user->set('name', 'Jane');

echo $user->name;  // "Jane"
```

### Conditional Updates

```php
$user = UserData::from(['name' => 'John', 'age' => 30]);

// Only update if condition is met:
if ($user->age < 18) {
    $user->set('status', 'minor');
} else {
    $user->set('status', 'adult');
}
```

---

## When to Use Data

### ✅ Use Data (Mutable) For:

#### 1. Form Handling

```php
class ContactFormData extends Data
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $message = '',
        public bool $agreedToTerms = false,
    ) {}
}

// Populate from request:
$form = ContactFormData::from($request->all());

// Validate:
if (empty($form->name)) {
    $form->name = 'Anonymous';
}

// Process:
$this->sendEmail($form);
```

#### 2. Builder Pattern

```php
class QueryBuilderData extends Data
{
    public function __construct(
        public string $table = '',
        public array $select = [],
        public array $where = [],
        public int $limit = 10,
    ) {}
    
    public function addSelect(string $field): self
    {
        $this->select[] = $field;
        return $this;
    }
    
    public function addWhere(string $field, mixed $value): self
    {
        $this->where[$field] = $value;
        return $this;
    }
}

$query = QueryBuilderData::from(['table' => 'users'])
    ->addSelect('id')
    ->addSelect('name')
    ->addWhere('status', 'active');
```

#### 3. Stateful Operations

```php
class ProcessorData extends Data
{
    public function __construct(
        public array $items = [],
        public int $processed = 0,
        public array $errors = [],
    ) {}
    
    public function process(callable $handler): void
    {
        foreach ($this->items as $item) {
            try {
                $handler($item);
                $this->processed++;
            } catch (\Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }
    }
}
```

#### 4. Temporary/Working Data

```php
class CalculationData extends Data
{
    public function __construct(
        public float $subtotal = 0.0,
        public float $tax = 0.0,
        public float $discount = 0.0,
        public float $total = 0.0,
    ) {}
    
    public function calculate(): void
    {
        $this->subtotal = $this->calculateSubtotal();
        $this->tax = $this->subtotal * 0.08;
        $this->total = $this->subtotal + $this->tax - $this->discount;
    }
}
```

---

### ❌ Use Dto (Immutable) For:

#### 1. API Responses

```php
class UserDto extends Dto  // NOT Data
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}
```

#### 2. Value Objects

```php
class MoneyDto extends Dto  // NOT Data
{
    public function __construct(
        public readonly int $amount,
        public readonly string $currency,
    ) {}
}
```

#### 3. Domain Models

```php
class OrderDto extends Dto  // NOT Data
{
    public function __construct(
        public readonly int $id,
        public readonly UserDto $customer,
        public readonly array $items,
        public readonly float $total,
    ) {}
}
```

---

## Best Practices

### 1. Choose the Right Base Class

✅ **DO:**
```php
// For forms, builders, stateful objects:
class FormData extends Data { }

// For API responses, value objects:
class ResponseDto extends Dto { }
```

❌ **DON'T:** Mix concerns
```php
// Don't use Data for API responses:
class UserResponseData extends Data { }  // Should be Dto

// Don't use Dto for forms:
class FormDto extends Dto { }  // Should be Data
```

---

### 2. Don't Use readonly with Data

✅ **DO:**
```php
class UserData extends Data
{
    public function __construct(
        public string $name,      // No readonly
        public int $age,
    ) {}
}
```

❌ **DON'T:**
```php
class UserData extends Data
{
    public function __construct(
        public readonly string $name,  // Don't use readonly with Data
        public readonly int $age,
    ) {}
}
// This defeats the purpose of Data (mutability)
```

---

### 3. Validate After Mutations

✅ **DO:**
```php
$form = FormData::from($input, new Context(validationEnabled: true));

// After mutation, validate again if needed:
$form->email = $newEmail;
// Re-validate manually or before saving
```

---

### 4. Use Type Hints

✅ **DO:**
```php
class SettingsData extends Data
{
    public function __construct(
        public bool $enabled,
        public int $timeout,
    ) {}
}
```

❌ **DON'T:**
```php
class SettingsData extends Data
{
    public function __construct(
        public $enabled,     // Missing type hint
        public $timeout,
    ) {}
}
```

---

## Common Patterns

### 1. Form Processing

```php
class RegistrationFormData extends Data
{
    public function __construct(
        public string $username = '',
        public string $email = '',
        public string $password = '',
        public string $passwordConfirmation = '',
        public bool $agreedToTerms = false,
    ) {}
    
    public function validate(): array
    {
        $errors = [];
        
        if (strlen($this->password) < 8) {
            $errors['password'] = 'Password must be at least 8 characters';
        }
        
        if ($this->password !== $this->passwordConfirmation) {
            $errors['password_confirmation'] = 'Passwords do not match';
        }
        
        if (!$this->agreedToTerms) {
            $errors['terms'] = 'You must agree to the terms';
        }
        
        return $errors;
    }
}

// Controller:
$form = RegistrationFormData::from($request->all());

if ($errors = $form->validate()) {
    return back()->withErrors($errors);
}

// Clear confirmation field before saving:
$form->passwordConfirmation = '';

$user = User::create($form->toArray());
```

---

### 2. Wizard/Multi-Step Form

```php
class WizardData extends Data
{
    public function __construct(
        public int $currentStep = 1,
        public array $step1Data = [],
        public array $step2Data = [],
        public array $step3Data = [],
    ) {}
    
    public function updateStep(int $step, array $data): void
    {
        $this->currentStep = $step;
        $this->{"step{$step}Data"} = $data;
    }
    
    public function nextStep(): void
    {
        $this->currentStep++;
    }
    
    public function isComplete(): bool
    {
        return $this->currentStep > 3
            && !empty($this->step1Data)
            && !empty($this->step2Data)
            && !empty($this->step3Data);
    }
}
```

---

### 3. Builder Pattern

```php
class EmailData extends Data
{
    public function __construct(
        public string $to = '',
        public string $subject = '',
        public string $body = '',
        public array $attachments = [],
        public array $cc = [],
        public array $bcc = [],
    ) {}
    
    public function to(string $email): self
    {
        $this->to = $email;
        return $this;
    }
    
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }
    
    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }
    
    public function attach(string $file): self
    {
        $this->attachments[] = $file;
        return $this;
    }
}

// Usage:
$email = (new EmailData())
    ->to('user@example.com')
    ->subject('Welcome!')
    ->body('Thanks for signing up')
    ->attach('/path/to/file.pdf');

$mailer->send($email);
```

---

## Troubleshooting

### Issue: Cannot Modify Property

**Problem:**
```php
class UserData extends Data
{
    public function __construct(
        public readonly string $name,  // readonly!
    ) {}
}

$user = UserData::from(['name' => 'John']);
$user->name = 'Jane';  // Error: Cannot modify readonly property
```

**Solution:** Remove `readonly`
```php
public string $name,  // Not readonly
```

---

### Issue: Type Mismatch on Assignment

**Problem:**
```php
$user = UserData::from(['age' => 30]);
$user->age = 'thirty';  // Type error: string instead of int
```

**Solution:** Assign correct type
```php
$user->age = 30;  // int
```

---

### Issue: update() Method Not Found

**Problem:**
```php
class UserDto extends Dto  // Using Dto, not Data!
{
    // ...
}

$user = UserDto::from(['name' => 'John']);
$user->update(['name' => 'Jane']);  // Error: update() not available
```

**Solution:** Use Data class or use with() for immutable updates
```php
// Option 1: Use Data
class UserData extends Data { }
$user = UserData::from(['name' => 'John']);
$user->update(['name' => 'Jane']);

// Option 2: Use with() for immutable update
class UserDto extends Dto { }
$user = UserDto::from(['name' => 'John']);
$updated = $user->with(['name' => 'Jane']);  // Returns new instance
```

---

## Summary

- ✅ **Data** for mutable objects (forms, builders, stateful)
- ✅ **Dto** for immutable objects (API responses, value objects)
- ✅ **update()** and **set()** methods for batch/single property updates
- ✅ **Same features** as Dto (hydration, casting, validation)
- ⚠️ **Not thread-safe** unlike immutable Dto
- ✅ **Use case driven** - choose based on your needs

---

**Next:** [Best Practices](./best-practices.md) | [Troubleshooting](./troubleshooting.md)
