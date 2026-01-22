# 📚 Jooservices DTO Library - Documentation

Welcome to the comprehensive documentation for **jooservices/dto** - a powerful PHP 8.5+ library for Data Transfer Objects (DTOs) and mutable Data objects.

[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](../LICENSE)
[![Latest Version](https://img.shields.io/badge/version-1.0.0-orange)](https://packagist.org/packages/jooservices/dto)

---

## 🚀 Quick Start

```php
use JOOservices\Dto\Core\Dto;

// Define your DTO
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly int $age,
    ) {}
}

// Create from array
$user = UserDto::from([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 30
]);

// Access properties
echo $user->name;  // John Doe

// Convert to array
$array = $user->toArray();
```

---

## ✨ Key Features

| Feature | Description |
|---------|-------------|
| 🔒 **Immutable DTOs** | Type-safe, immutable data transfer objects |
| ✏️ **Mutable Data Objects** | Flexible mutable data containers |
| 🔄 **Type Casting** | Automatic type conversion (scalars, DateTime, Enums, arrays) |
| ✅ **Validation** | Built-in validation with custom rules |
| 💧 **Hydration** | Create from arrays, JSON, or objects |
| 📤 **Normalization** | Export to arrays or JSON |
| 🎨 **Custom Casters** | Extend with your own type casters |
| 🔧 **Transformers** | Custom output transformations |
| 📝 **Naming Strategies** | camelCase, snake_case support |
| ⚡ **Performance** | Optimized with metadata caching |

---

## 📖 Documentation Structure

This documentation is organized progressively from beginner to advanced topics:

### 1️⃣ [Getting Started](./01-getting-started/)
Start here if you're new to the library.
- [Installation](./01-getting-started/installation.md) - Requirements & setup
- [Quick Start](./01-getting-started/quick-start.md) - 5-minute guide
- [Basic Concepts](./01-getting-started/basic-concepts.md) - Core principles

### 2️⃣ [User Guide](./02-user-guide/)
Comprehensive guides for everyday usage.
- [Creating DTOs](./02-user-guide/creating-dtos.md)
- [Type Casting](./02-user-guide/type-casting.md)
- [Validation](./02-user-guide/validation.md)
- [Nested Objects](./02-user-guide/nested-objects.md)
- [Arrays & Collections](./02-user-guide/arrays-collections.md)
- [Data Objects](./02-user-guide/data-objects.md)
- [Best Practices](./02-user-guide/best-practices.md)
- [Troubleshooting](./02-user-guide/troubleshooting.md)

### 3️⃣ [Examples](./03-examples/)
Real-world code examples and use cases.
- [Basic Examples](./03-examples/basic-examples.md)
- [API Integration](./03-examples/api-integration.md)
- [Form Handling](./03-examples/form-handling.md)
- [Database Mapping](./03-examples/database-mapping.md)
- [Real-World Scenarios](./03-examples/real-world-scenarios.md)
- [Code Samples](./03-examples/code-samples/) - Runnable examples

### 4️⃣ [Development](./04-development/)
Contributing and development guides.
- [Linting Standards](./04-development/linting-standards.md) - Code quality tools and standards
- [Secret Scanning](./04-development/secret-scanning.md) - Security setup and configuration
- [Ignore Files](./04-development/ignore-files.md) - Understanding project ignore files
- [Setup](./04-development/setup.md) - Dev environment (future)
- [Contributing](./04-development/contributing.md) - How to contribute (future)
- [Coding Standards](./04-development/coding-standards.md) - PSR-12 & conventions (future)
- [Testing](./04-development/testing.md) - Writing tests (future)
- [CI/CD](./04-development/ci-cd.md) - Pipeline explained (future)
- [Release Process](./04-development/release-process.md) - How releases work (future)

### 5️⃣ [API Reference](./05-api-reference/)
Complete API documentation.
- [DTO Class](./05-api-reference/dto-class.md)
- [Data Class](./05-api-reference/data-class.md)
- [Attributes](./05-api-reference/attributes.md)
- [Type Casters](./05-api-reference/type-casters.md)
- [Interfaces](./05-api-reference/interfaces.md)

### 6️⃣ [Advanced](./06-advanced/)
Advanced topics and customization.
- [Custom Casters](./06-advanced/custom-casters.md)
- [Custom Validators](./06-advanced/custom-validators.md)
- [Performance](./06-advanced/performance.md)
- [Architecture](./06-advanced/architecture.md)
- [Extending](./06-advanced/extending.md)

### 7️⃣ [Migration](./07-migration/)
Upgrade guides and migration help.
- [From Arrays](./07-migration/from-arrays.md)
- [From Other Libraries](./07-migration/from-other-libraries.md)
- [Upgrade Guide](./07-migration/upgrade-guide.md)

---

## 🎯 Common Use Cases

### 🌐 **API Development**
Perfect for handling REST API requests and responses with type safety.

### 📝 **Form Processing**
Process and validate form data with ease.

### 🗄️ **Database Mapping**
Map database records to type-safe objects.

### 🔄 **Data Transformation**
Transform data between different formats and structures.

### 📦 **Data Validation**
Ensure data integrity with built-in validation.

---

## 🔗 Quick Links

| Resource | Link |
|----------|------|
| 📦 **GitHub** | [github.com/jooservices/dto](https://github.com/jooservices/dto) |
| 📥 **Packagist** | [packagist.org/packages/jooservices/dto](https://packagist.org/packages/jooservices/dto) |
| 🐛 **Issues** | [Report Bugs](https://github.com/jooservices/dto/issues) |
| 💬 **Discussions** | [Ask Questions](https://github.com/jooservices/dto/discussions) |
| 📧 **Contact** | contact@jooservices.com |

---

## 🎓 Learning Path

We recommend following this learning path:

```
1. Installation (5 minutes)
   └─> Install via Composer

2. Quick Start (10 minutes)
   └─> Create your first DTO

3. Basic Concepts (15 minutes)
   └─> Understand DTO vs Data

4. User Guide (1-2 hours)
   └─> Learn all features

5. Examples (30 minutes)
   └─> See real-world usage

6. Advanced Topics (as needed)
   └─> Customize & extend
```

---

## 💡 Need Help?

- **Quick Question?** Check the [Troubleshooting Guide](./02-user-guide/troubleshooting.md)
- **How-To?** Browse the [Examples](./03-examples/)
- **API Details?** See the [API Reference](./05-api-reference/)
- **Bug or Issue?** [Open an Issue](https://github.com/jooservices/dto/issues)
- **Discussion?** [Join Discussions](https://github.com/jooservices/dto/discussions)

---

## 📄 License

This library is open-sourced software licensed under the [MIT license](../LICENSE).

---

## 🙏 Acknowledgments

Built with ❤️ by [JOOservices](https://jooservices.com)

Special thanks to all [contributors](https://github.com/jooservices/dto/graphs/contributors) who have helped improve this library.

---

**Ready to get started?** → [Installation Guide](./01-getting-started/installation.md)
