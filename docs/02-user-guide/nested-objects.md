# 🏗️ Nested Objects

Complete guide to working with nested DTOs in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Simple Nested DTOs](#simple-nested-dtos)
3. [Deeply Nested Structures](#deeply-nested-structures)
4. [Arrays of Nested DTOs](#arrays-of-nested-dtos)
5. [Optional Nested Objects](#optional-nested-objects)
6. [Polymorphic Nesting](#polymorphic-nesting)
7. [Best Practices](#best-practices)
8. [Common Patterns](#common-patterns)
9. [Troubleshooting](#troubleshooting)

---

## Introduction

Nested DTOs allow you to compose complex data structures from simpler DTOs. The library automatically hydrates nested objects during the creation process.

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
        public readonly AddressDto $address,  // Nested DTO
    ) {}
}

$user = UserDto::from([
    'name' => 'John Doe',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
    ],
]);

echo $user->address->city;  // "New York"
```

---

## Simple Nested DTOs

### Basic Example

```php
class CompanyDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {}
}

class EmployeeDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly CompanyDto $company,
    ) {}
}

// Hydrate from nested array:
$employee = EmployeeDto::from([
    'name' => 'Jane Smith',
    'company' => [
        'name' => 'Acme Corp',
    ],
]);

echo $employee->company->name;  // "Acme Corp"
```

### From JSON

```php
$json = '{
    "name": "Jane Smith",
    "company": {
        "name": "Acme Corp"
    }
}';

$employee = EmployeeDto::fromJson($json);
```

### From Objects

```php
$data = (object)[
    'name' => 'Jane Smith',
    'company' => (object)[
        'name' => 'Acme Corp',
    ],
];

$employee = EmployeeDto::from($data);
```

---

## Deeply Nested Structures

You can nest DTOs as deeply as needed:

```php
class CityDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {}
}

class CountryDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly CityDto $capital,
    ) {}
}

class ContinentDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var CountryDto[] */
        public readonly array $countries,
    ) {}
}

$continent = ContinentDto::from([
    'name' => 'Europe',
    'countries' => [
        [
            'name' => 'France',
            'capital' => ['name' => 'Paris'],
        ],
        [
            'name' => 'Germany',
            'capital' => ['name' => 'Berlin'],
        ],
    ],
]);

// Access deeply nested data:
echo $continent->countries[0]->capital->name;  // "Paris"
```

---

## Arrays of Nested DTOs

### Simple Array

```php
class TagDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {}
}

class ArticleDto extends Dto
{
    public function __construct(
        public readonly string $title,
        /** @var TagDto[] */
        public readonly array $tags,
    ) {}
}

$article = ArticleDto::from([
    'title' => 'My Article',
    'tags' => [
        ['name' => 'PHP'],
        ['name' => 'DTO'],
        ['name' => 'Tutorial'],
    ],
]);

foreach ($article->tags as $tag) {
    echo $tag->name . "\n";
}
```

### Nested Arrays with Complex Objects

```php
class CommentDto extends Dto
{
    public function __construct(
        public readonly string $text,
        public readonly UserDto $author,
    ) {}
}

class PostDto extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly UserDto $author,
        /** @var CommentDto[] */
        public readonly array $comments,
    ) {}
}

$post = PostDto::from([
    'title' => 'Hello World',
    'author' => [
        'name' => 'John',
        'address' => [
            'street' => '123 Main St',
            'city' => 'NYC',
        ],
    ],
    'comments' => [
        [
            'text' => 'Great post!',
            'author' => [
                'name' => 'Jane',
                'address' => [
                    'street' => '456 Oak Ave',
                    'city' => 'LA',
                ],
            ],
        ],
    ],
]);
```

---

## Optional Nested Objects

### Nullable Nested DTOs

```php
class ProfileDto extends Dto
{
    public function __construct(
        public readonly string $bio,
    ) {}
}

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly ?ProfileDto $profile,  // Optional
    ) {}
}

// With profile:
$user1 = UserDto::from([
    'name' => 'John',
    'profile' => ['bio' => 'Developer'],
]);

// Without profile:
$user2 = UserDto::from([
    'name' => 'Jane',
    'profile' => null,
]);

// Missing profile (treated as null):
$user3 = UserDto::from([
    'name' => 'Bob',
]);
```

### Using Optional<T>

```php
use JOOservices\Dto\Core\Optional;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly Optional $profile,  // Optional<ProfileDto>
    ) {}
}

$user = UserDto::from([
    'name' => 'John',
    // profile not provided
]);

if ($user->profile->isPresent()) {
    echo $user->profile->get()->bio;
} else {
    echo "No profile";
}
```

---

## Polymorphic Nesting

Handle different types of nested objects:

```php
use JOOservices\Dto\Attributes\DiscriminatorMap;

abstract class PaymentDto extends Dto
{
    public function __construct(
        public readonly float $amount,
    ) {}
}

class CreditCardPaymentDto extends PaymentDto
{
    public function __construct(
        float $amount,
        public readonly string $cardNumber,
    ) {
        parent::__construct($amount);
    }
}

class PayPalPaymentDto extends PaymentDto
{
    public function __construct(
        float $amount,
        public readonly string $email,
    ) {
        parent::__construct($amount);
    }
}

class OrderDto extends Dto
{
    public function __construct(
        public readonly int $id,
        #[DiscriminatorMap([
            'credit_card' => CreditCardPaymentDto::class,
            'paypal' => PayPalPaymentDto::class,
        ], 'payment_type')]
        public readonly PaymentDto $payment,
    ) {}
}

// Credit card payment:
$order1 = OrderDto::from([
    'id' => 1,
    'payment' => [
        'payment_type' => 'credit_card',
        'amount' => 99.99,
        'card_number' => '1234-5678-9012-3456',
    ],
]);

// PayPal payment:
$order2 = OrderDto::from([
    'id' => 2,
    'payment' => [
        'payment_type' => 'paypal',
        'amount' => 49.99,
        'email' => 'user@example.com',
    ],
]);
```

---

## Best Practices

### 1. Keep DTOs Small and Focused

✅ **DO:**
```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
    ) {}
}

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,  // Separate DTO
    ) {}
}
```

❌ **DON'T:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $street,        // Flat structure
        public readonly string $city,
        public readonly string $country,
    ) {}
}
```

---

### 2. Use Type Hints

✅ **DO:**
```php
class PostDto extends Dto
{
    public function __construct(
        public readonly UserDto $author,      // Type hint present
    ) {}
}
```

❌ **DON'T:**
```php
class PostDto extends Dto
{
    public function __construct(
        public readonly $author,              // Missing type hint
    ) {}
}
```

---

### 3. Document Array Types

✅ **DO:**
```php
class PostDto extends Dto
{
    public function __construct(
        /** @var CommentDto[] */
        public readonly array $comments,
    ) {}
}
```

❌ **DON'T:**
```php
class PostDto extends Dto
{
    public function __construct(
        public readonly array $comments,      // No type documentation
    ) {}
}
```

---

### 4. Use Readonly for Immutability

✅ **DO:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,  // Readonly
    ) {}
}
```

---

## Common Patterns

### 1. Builder Pattern with Nested DTOs

```php
class UserDtoBuilder
{
    private array $data = [];

    public function withName(string $name): self
    {
        $this->data['name'] = $name;
        return $this;
    }

    public function withAddress(AddressDto $address): self
    {
        $this->data['address'] = $address;
        return $this;
    }

    public function build(): UserDto
    {
        return UserDto::from($this->data);
    }
}

$user = (new UserDtoBuilder())
    ->withName('John')
    ->withAddress(AddressDto::from(['street' => '123 Main', 'city' => 'NYC']))
    ->build();
```

---

### 2. Nested DTOs from API Responses

```php
class ApiResponse
{
    public function getUserData(): array
    {
        return [
            'user' => [
                'name' => 'John',
                'profile' => [
                    'bio' => 'Developer',
                    'avatar' => 'https://example.com/avatar.jpg',
                ],
                'posts' => [
                    [
                        'title' => 'First Post',
                        'comments' => [
                            ['text' => 'Nice!', 'author' => 'Jane'],
                        ],
                    ],
                ],
            ],
        ];
    }
}

$data = (new ApiResponse())->getUserData();
$user = UserDto::from($data['user']);
```

---

### 3. Partial Updates with Nested Objects

```php
$original = UserDto::from([
    'name' => 'John',
    'address' => [
        'street' => '123 Main St',
        'city' => 'NYC',
    ],
]);

// Update only the address:
$updated = $original->with([
    'address' => AddressDto::from([
        'street' => '456 Oak Ave',
        'city' => 'LA',
    ]),
]);
```

---

## Troubleshooting

### Issue: Nested DTO Not Hydrating

**Problem:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly $address,  // No type hint!
    ) {}
}
```

**Solution:** Add type hint
```php
public readonly AddressDto $address,
```

---

### Issue: Array of DTOs Not Working

**Problem:**
```php
public readonly array $comments;  // No documentation
```

**Solution:** Add PHPDoc
```php
/** @var CommentDto[] */
public readonly array $comments;
```

---

### Issue: Circular References

**Problem:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly PostDto $latestPost,
    ) {}
}

class PostDto extends Dto
{
    public function __construct(
        public readonly UserDto $author,  // Circular!
    ) {}
}
```

**Solution:** Break the cycle with nullable or lazy loading:
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly ?PostDto $latestPost,  // Nullable
    ) {}
}
```

---

### Issue: Deep Nesting Performance

**Problem:** Very deep nesting causing performance issues

**Solution:** Use lazy loading or computed properties:
```php
use JOOservices\Dto\Attributes\Computed;
use JOOservices\Dto\Core\ComputesLazyProperties;

class UserDto extends Dto implements ComputesLazyProperties
{
    public function __construct(
        public readonly int $userId,
    ) {}

    #[Computed]
    public function profile(): ProfileDto
    {
        // Load only when accessed
        return ProfileDto::from($this->loadProfile($this->userId));
    }
}
```

---

## Summary

- ✅ **Automatic hydration** of nested DTOs
- ✅ **Deep nesting** supported without limits
- ✅ **Arrays of DTOs** with type documentation
- ✅ **Optional nesting** with nullable or Optional<T>
- ✅ **Polymorphic nesting** with DiscriminatorMap
- ✅ **Type-safe** with proper type hints

---

**Next:** [Arrays & Collections](./arrays-collections.md) | [Data Objects](./data-objects.md)
