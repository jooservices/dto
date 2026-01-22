# JOOservices DTO Library

[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)
[![Latest Version](https://img.shields.io/badge/version-1.0.0-orange.svg)](https://packagist.org/packages/jooservices/dto)

A modern PHP 8.5+ library for building type-safe, immutable Data Transfer Objects (DTOs) with powerful hydration, validation, and transformation capabilities.

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🔒 **Immutable DTOs** | Type-safe, readonly data transfer objects |
| ✏️ **Mutable Data Objects** | Flexible mutable data containers via `Data` class |
| 💧 **Multi-Source Hydration** | Create from arrays, JSON, objects with `from()`, `fromArray()`, `fromJson()` |
| 🔄 **Type Casting** | Automatic type conversion (scalars, DateTime, Enums, nested DTOs, arrays) |
| ✅ **Validation** | Built-in validation attributes (`@Required`, `@Email`, `@Valid`) |
| 🎨 **Custom Casters** | Extensible type casting system via `CasterInterface` |
| 🔧 **Custom Transformers** | Output transformation pipeline with `TransformerInterface` |
| 📝 **Naming Strategies** | Automatic `camelCase` ↔ `snake_case` conversion |
| 📚 **Collections** | `DataCollection` and `PaginatedCollection` support |
| 📋 **Schema Generators** | Export to JSON Schema and OpenAPI 3.0 specifications |
| ⚡ **Pipeline System** | Global and per-property data transformation pipelines |
| 🛠️ **Utility Methods** | `diff()`, `equals()`, `hash()`, `merge()`, `clone()`, `when()`, `unless()` |
| 🔀 **Optional<T>** | Type-safe optional value wrapper for handling missing data |
| 🎯 **Lifecycle Hooks** | `transformInput()`, `afterHydration()`, `beforeSerialization()` |
| ⚡ **Computed Properties** | WeakMap-based cached lazy-evaluated properties |
| 🏗️ **Polymorphism** | `@DiscriminatorMap` for polymorphic type mapping |

---

## 🚀 Quick Start

### Installation

```bash
composer require jooservices/dto
```

**Requirements:** PHP 8.5 or higher

### Basic Usage

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\Validation\Required;

class UserDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $name,
        
        #[MapFrom('email_address')]
        public readonly string $email,
        
        public readonly int $age,
    ) {}
}

// Create from array
$user = UserDto::from([
    'name' => 'John Doe',
    'email_address' => 'john@example.com',
    'age' => 30
]);

// Access properties
echo $user->name;  // John Doe
echo $user->email; // john@example.com

// Convert to array
$array = $user->toArray();

// Convert to JSON
$json = $user->toJson();
```

### Advanced Example: Nested DTOs and Collections

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
    ) {}
}

class UserProfileDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
        /** @var array<TagDto> */
        public readonly array $tags,
    ) {}
}

$profile = UserProfileDto::from([
    'name' => 'Jane Smith',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York',
        'country' => 'USA'
    ],
    'tags' => [
        ['name' => 'developer'],
        ['name' => 'php']
    ]
]);
```

---

## 📖 Documentation

Comprehensive documentation is available in the [`./docs`](./docs) directory:

- **[Getting Started](./docs/01-getting-started/)** - Installation, quick start, basic concepts
- **[User Guide](./docs/02-user-guide/)** - Creating DTOs, validation, best practices, troubleshooting
- **[Examples](./docs/03-examples/)** - Real-world usage examples and API integration patterns

👉 **Start here:** [Documentation Hub](./docs/README.md)

---

## 🎯 Use Cases

- **REST API Development** - Type-safe request/response handling
- **Form Processing** - Validate and transform user input
- **Database Mapping** - Map database records to typed objects
- **Microservices** - Consistent data contracts between services
- **Data Validation** - Ensure data integrity with built-in validation
- **API Clients** - Type-safe API response objects

---

## 🔗 Links

| Resource | URL |
|----------|-----|
| 📦 **Packagist** | [packagist.org/packages/jooservices/dto](https://packagist.org/packages/jooservices/dto) |
| 💻 **GitHub** | [github.com/jooservices/dto](https://github.com/jooservices/dto) |
| 🐛 **Issue Tracker** | [github.com/jooservices/dto/issues](https://github.com/jooservices/dto/issues) |
| 📧 **Contact** | contact@jooservices.com |

---

## 🤝 Contributing

Contributions are welcome! Please see our contributing guidelines in the documentation for details on:

- Code style and standards
- Running tests
- Submitting pull requests
- Reporting issues

---

## 📜 License

This library is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🙏 Credits

Developed and maintained by **[JOOservices](https://jooservices.com)**

Special thanks to all [contributors](https://github.com/jooservices/dto/graphs/contributors) who have helped improve this library.

---

**Ready to build type-safe DTOs?** → [Get Started](./docs/01-getting-started/installation.md)
