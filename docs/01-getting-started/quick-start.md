# ⚡ Quick Start Guide

Get up and running with **jooservices/dto** in 5 minutes!

---

## 🎯 Your First DTO

Let's create a simple `UserDto`:

```php
<?php

use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}
```

### Create from Array

```php
$user = UserDto::from([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);
```

### Access Properties

```php
echo $user->name;   // John Doe
echo $user->email;  // john@example.com
echo $user->age;    // 30
```

### Convert to Array

```php
$array = $user->toArray();
// ['name' => 'John Doe', 'email' => 'john@example.com', 'age' => 30]
```

---

## ✏️ Mutable Data Objects

Need to modify data after creation? Use `Data`:

```php
use JOOservices\Dto\Core\Data;

class UserData extends Data
{
    public string $name;
    public string $email;
    public int $age;
}

// Create and modify
$user = new UserData();
$user->name = 'Jane Doe';
$user->email = 'jane@example.com';
$user->age = 25;

// Or from array
$user = UserData::from([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'age' => 25
]);

// Modify after creation
$user->age = 26; // ✅ Allowed with Data objects
```

---

## 🏗️ Nested DTOs

Create complex structures with nested objects:

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
        public readonly string $email,
        public readonly AddressDto $address,
    ) {}
}

// Create with nested data
$user = UserDto::from([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
        'zipCode' => '10001'
    ]
]);

// Access nested properties
echo $user->address->street;  // 123 Main St
echo $user->address->city;    // New York
```

---

## 🔄 Automatic Type Casting

The library automatically casts types:

```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly DateTime $createdAt,
        public readonly bool $inStock,
    ) {}
}

$product = ProductDto::from([
    'name' => 'Laptop',
    'price' => '999.99',          // String → float
    'created_at' => '2024-01-20', // String → DateTime
    'in_stock' => 1,              // Int → bool
]);

echo $product->price;             // 999.99 (float)
echo $product->createdAt->format('Y-m-d'); // 2024-01-20
var_dump($product->inStock);      // bool(true)
```

---

## 📋 Arrays of Objects

Handle collections of DTOs:

```php
class OrderDto extends Dto
{
    public function __construct(
        public readonly string $orderId,
        /** @var ProductDto[] */
        public readonly array $products,
    ) {}
}

$order = OrderDto::from([
    'order_id' => 'ORD-123',
    'products' => [
        ['name' => 'Laptop', 'price' => 999.99, /* ... */],
        ['name' => 'Mouse', 'price' => 29.99, /* ... */],
    ]
]);

// Access items
foreach ($order->products as $product) {
    echo $product->name . ': $' . $product->price . "\n";
}
```

---

## 🎨 Using Attributes

Customize behavior with attributes:

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\Hidden;

class ApiUserDto extends Dto
{
    public function __construct(
        #[MapFrom('full_name')]
        public readonly string $name,
        
        public readonly string $email,
        
        #[Hidden]
        public readonly string $password,
    ) {}
}

$user = ApiUserDto::from([
    'full_name' => 'John Doe',  // Maps to 'name'
    'email' => 'john@example.com',
    'password' => 'secret123'
]);

$array = $user->toArray();
// ['name' => 'John Doe', 'email' => 'john@example.com']
// Note: 'password' is hidden
```

---

## 🎯 Complete Example

Putting it all together:

```php
<?php

require 'vendor/autoload.php';

use JOOservices\Dto\Core\Dto;

// Define DTOs
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
        public readonly string $email,
        public readonly int $age,
        public readonly AddressDto $address,
    ) {}
}

// Create from API response (array)
$apiResponse = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30,
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York'
    ]
];

$user = UserDto::from($apiResponse);

// Use the DTO
echo "User: {$user->name}\n";
echo "Email: {$user->email}\n";
echo "Lives at: {$user->address->street}, {$user->address->city}\n";

// Convert back to array for database/API
$data = $user->toArray();
```

---

## 🚀 What's Next?

You're now ready to use DTOs in your project! Continue learning:

### 📚 **Learn More:**
- [Basic Concepts](./basic-concepts.md) - Understand DTO vs Data
- [Type Casting Guide](../02-user-guide/type-casting.md) - Learn about automatic casting
- [Validation](../02-user-guide/validation.md) - Add validation rules
- [Best Practices](../02-user-guide/best-practices.md) - Write better code

### 💡 **See Examples:**
- [API Integration](../03-examples/api-integration.md) - REST API examples
- [Form Handling](../03-examples/form-handling.md) - Process form data
- [Real-World Scenarios](../03-examples/real-world-scenarios.md) - Production use cases

### 🔧 **Advanced Topics:**
- [Custom Casters](../06-advanced/custom-casters.md) - Create custom type casters
- [Performance](../06-advanced/performance.md) - Optimize for production
- [Architecture](../06-advanced/architecture.md) - Understand internals

---

**Need help?** Check the [Troubleshooting Guide](../02-user-guide/troubleshooting.md) or [ask a question](https://github.com/jooservices/dto/discussions).
