# Linting Standards & Priority

This document outlines the code quality tools and their priority order for the DTO library.

## 🎯 Priority Order

**Pint (Laravel Standard) is the TOP PRIORITY** - All other linting tools are configured to align with Pint's Laravel coding standard.

### Priority Hierarchy (Active in CI/Hooks)

1. **Pint** (Laravel Standard) - 🥇 **PRIMARY CODE STYLE ENFORCER**
2. **PHPCS** (PHP_CodeSniffer) - Code standards verification
3. **PHPStan** - Static analysis

### Additional Tools (Available but Not in Main Lint)

- **PHPMD** (PHP Mess Detector) - Code quality metrics (disabled due to PHP 8.5 compatibility issues)
- **PHP-CS-Fixer** - Additional fixes (conflicts with Pint's Laravel standard, use `lint:all` if needed)

## 📋 Tool Configuration

### 1. Pint (Laravel Standard) 🥇

**Configuration:** `pint.json`

Pint is Laravel's opinionated code style tool built on PHP-CS-Fixer. It uses the Laravel preset as the primary standard.

**Key Features:**
- Laravel preset with strict typing
- Ordered imports (class, function, const)
- Global namespace imports
- Trailing commas in multiline structures
- Nullable type declarations

**Usage:**
```bash
# Check code style
composer pint:test

# Fix code style
composer pint
```

### 2. PHPCS (PHP_CodeSniffer)

**Configuration:** `phpcs.xml`

PHPCS is configured to follow PSR-12 (Laravel's base standard) with additional Laravel-compatible rules.

**Key Features:**
- PSR-12 standard
- 120 character line limit (Laravel standard)
- Disallow long array syntax
- Laravel-compatible whitespace and formatting

**Usage:**
```bash
# Check code standards
composer phpcs

# Auto-fix code standards
composer phpcbf
```

### 3. PHPStan

**Configuration:** `phpstan.neon`

Static analysis tool for detecting bugs and type issues.

**Level:** Maximum strictness
**Extensions:** Strict rules, PHPUnit rules

**Usage:**
```bash
composer phpstan
```

### 4. PHPMD (PHP Mess Detector)

**Configuration:** `phpmd.xml`

Detects code smells, complexity issues, and potential bugs.

**Usage:**
```bash
composer phpmd
```

### 5. PHP-CS-Fixer

**Configuration:** `.php-cs-fixer.dist.php`

Configured to align with Laravel/Pint standards. Key adjustments:
- Opening braces follow Laravel convention
- No spacing around concatenation (Laravel style)
- Simplified if return disabled (Laravel preference)

**Usage:**
```bash
# Check for fixable issues
composer cs:check

# Apply fixes
composer cs:fix
```

## 🔄 Conflict Resolution

When there are conflicts between linting tools:

1. **Pint's rules take precedence** - Adjust other tools to match Pint
2. **PHP-CS-Fixer** is configured to match Pint's Laravel preset
3. **PHPCS** uses PSR-12 (Laravel's base) with Laravel-compatible rules
4. **PHPStan and PHPMD** focus on logic/quality, not style

## 🚀 Running Linters

### Quick Commands

```bash
# Run all linters (in priority order)
composer lint

# Fix all fixable issues
composer fix

# Individual tools
composer pint:test      # Pint check
composer pint           # Pint fix
composer phpcs          # PHPCS check
composer phpcbf         # PHPCS fix
composer phpstan        # Static analysis
composer phpmd          # Mess detection
composer cs:check       # PHP-CS-Fixer check
composer cs:fix         # PHP-CS-Fixer fix
```

### Git Hooks (CaptainHook)

Pre-commit hooks run in priority order:
1. PHP Linting (syntax check)
2. Gitleaks (secret scanning)
3. 🎨 **Pint** (Laravel Standard - TOP PRIORITY)
4. 📋 **PHPCS**
5. 🔍 **PHPStan**
6. 🔬 **PHPMD**
7. 🧹 **PHP-CS-Fixer**

### CI/CD Pipeline

The CI workflow runs linters in parallel with priority labels:
- Priority 1: Pint (Laravel Standard)
- Priority 2: PHPCS
- Priority 3: PHPStan
- Priority 4: PHPMD
- Priority 5: PHP-CS-Fixer

## 📝 Development Workflow

### Before Committing

```bash
# Fix all style issues
composer fix

# Verify all checks pass
composer lint

# Run tests
composer test
```

### Recommended IDE Setup

Configure your IDE to:
1. Use Pint for auto-formatting
2. Show PHPCS warnings inline
3. Show PHPStan errors inline
4. Format on save using Pint/Laravel standards

## 🎨 Laravel Standard Key Points

The Laravel standard (via Pint) includes:

- **PSR-12** as the base
- **Opening braces** on next line for classes and functions
- **No spaces** around string concatenation (`.`)
- **120 character** line limit
- **Short array** syntax (`[]` not `array()`)
- **Trailing commas** in multiline arrays/parameters
- **Strict types** declaration
- **Ordered imports** alphabetically
- **Global namespace** imports preferred

## 🔧 Troubleshooting

### Fixing Conflicts

If you encounter conflicts between tools:

1. **Run Pint first**: `composer pint`
2. **Check other tools**: `composer lint`
3. **If conflicts persist**: Pint's output is correct, adjust other tool configs

### Common Issues

**Issue**: PHP-CS-Fixer conflicts with Pint
**Solution**: Pint rules take precedence, PHP-CS-Fixer is configured to match

**Issue**: PHPCS reports style issues that Pint allows
**Solution**: Update `phpcs.xml` to exclude the rule or align with Laravel standard

**Issue**: PHPStan/PHPMD report style issues
**Solution**: These tools focus on logic/quality, not style - their reports are for different concerns

## 📚 References

- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [Laravel Coding Style](https://laravel.com/docs/contributions#coding-style)
- [PSR-12 Standard](https://www.php-fig.org/psr/psr-12/)
- [PHP-CS-Fixer Rules](https://cs.symfony.com/)
- [PHPCS Standards](https://github.com/squizlabs/PHP_CodeSniffer)

---

**Remember**: Pint is the source of truth for code style. All other tools are configured to complement, not conflict with, Laravel standards.
