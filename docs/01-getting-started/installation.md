# 📦 Installation

Get started with **jooservices/dto** in just a few minutes.

---

## System Requirements

Before installing, ensure your system meets these requirements:

| Requirement | Version | Status |
|-------------|---------|--------|
| **PHP** | 8.5 or higher | ✅ Required |
| **Composer** | 2.0+ | ✅ Required |
| **Extensions** | `json`, `mbstring` | ✅ Included |

### Check Your PHP Version

```bash
php -v
# PHP 8.5.x (cli) ...
```

---

## Installation Steps

### 1. Install via Composer

In your project directory, run:

```bash
composer require jooservices/dto
```

**Expected output:**
```
Using version ^1.0 for jooservices/dto
./composer.json has been updated
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
  - Locking jooservices/dto (1.0.0)
Writing lock file
Installing dependencies from lock file
Package operations: 1 install, 0 updates, 0 removals
  - Installing jooservices/dto (1.0.0): Extracting archive
Generating autoload files
```

### 2. Verify Installation

Check that the package is installed:

```bash
composer show jooservices/dto
```

**Expected output:**
```
name     : jooservices/dto
descrip. : A PHP 8.5+ DTO and Data library
versions : * 1.0.0
type     : library
license  : MIT License (MIT)
```

### 3. Test Autoloading

Create a test file to verify everything works:

```php
<?php
// test.php

require 'vendor/autoload.php';

use JOOservices\Dto\Core\Dto;

class TestDto extends Dto
{
    public function __construct(
        public readonly string $message
    ) {}
}

$dto = TestDto::from(['message' => 'Hello, DTO!']);
echo $dto->message; // Outputs: Hello, DTO!

echo "\n✅ Installation successful!\n";
```

Run it:
```bash
php test.php
# Hello, DTO!
# ✅ Installation successful!
```

---

## Directory Structure

After installation, your project will have:

```
your-project/
├── vendor/
│   └── jooservices/
│       └── dto/              # The library
│           ├── src/          # Source code
│           ├── tests/        # Test suite
│           ├── composer.json
│           └── README.md
├── composer.json             # Your dependencies
├── composer.lock
└── vendor/autoload.php       # Composer autoloader
```

---

## Configuration

The library works out-of-the-box with **zero configuration required**! 🎉

However, you can customize behavior using:

- **Naming Strategies** - `camelCase` vs `snake_case`
- **Custom Casters** - For special type conversions
- **Meta Caching** - For performance optimization

See the [User Guide](../02-user-guide/) for configuration options.

---

## Common Issues & Solutions

### Issue: "Class Not Found"

**Cause:** Autoloader not loaded  
**Solution:**
```php
// Make sure this is at the top of your file
require __DIR__ . '/vendor/autoload.php';
```

### Issue: "Composer require fails"

**Cause:** PHP version mismatch  
**Solution:**
```bash
# Check PHP version
php -v

# Update PHP to 8.5+
# On Ubuntu/Debian:
sudo apt install php8.5

# On macOS (Homebrew):
brew install php@8.5
```

### Issue: "mbstring extension not found"

**Solution:**
```bash
# Ubuntu/Debian
sudo apt install php8.5-mbstring

# macOS
# Already included in Homebrew PHP

# Windows
# Uncomment in php.ini:
# extension=mbstring
```

---

## Development Installation

If you want to contribute or run tests:

```bash
# Clone the repository
git clone https://github.com/jooservices/dto.git
cd dto

# Install dependencies
composer install

# Run tests
composer test

# Run linters
composer lint
```

---

## Next Steps

✅ **Installation complete!** Now you're ready to:

1. 📖 [Read the Quick Start Guide](./quick-start.md) - Create your first DTO (5 minutes)
2. 🎓 [Learn Basic Concepts](./basic-concepts.md) - Understand DTOs vs Data objects
3. 📚 [Explore the User Guide](../02-user-guide/) - Discover all features
4. 💡 [See Examples](../03-examples/) - Learn from real-world code

---

**Questions?** Check the [Troubleshooting Guide](../02-user-guide/troubleshooting.md) or [open an issue](https://github.com/jooservices/dto/issues).
