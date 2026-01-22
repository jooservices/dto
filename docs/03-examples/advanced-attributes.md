# 🎯 Advanced Attributes

Complete guide to advanced DTO attributes in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [DefaultFrom](#defaultfrom)
3. [Deprecated](#deprecated)
4. [StrictType](#stricttype)
5. [OptionalProperty](#optionalproperty)
6. [Real-World Examples](#real-world-examples)
7. [Best Practices](#best-practices)

---

## Introduction

Advanced attributes provide powerful capabilities for:

| Attribute | Purpose | Use Case |
|-----------|---------|----------|
| `#[DefaultFrom]` | Default values from config/env/method | Configuration management |
| `#[Deprecated]` | Mark properties as deprecated | API versioning, migrations |
| `#[StrictType]` | Force strict type checking | Type safety, data integrity |
| `#[OptionalProperty]` | Document optional properties | API documentation |

---

## DefaultFrom

**Specify default values from external sources when property is missing from input.**

### Signature

```php
#[DefaultFrom(
    config: ?string = null,   // Config key: 'app.timezone'
    env: ?string = null,      // Environment: 'APP_URL'
    method: ?string = null,   // Static method: 'generateDefault'
)]
```

### From Configuration

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Attributes\DefaultFrom;

// config/app.php
return [
    'timezone' => 'UTC',
    'locale' => 'en',
    'pagination' => [
        'per_page' => 25
    ]
];

class AppSettingsDto extends Dto
{
    public function __construct(
        #[DefaultFrom(config: 'app.timezone')]
        public readonly string $timezone,
        
        #[DefaultFrom(config: 'app.locale')]
        public readonly string $locale,
        
        #[DefaultFrom(config: 'app.pagination.per_page')]
        public readonly int $perPage,
    ) {}
}

// Usage
$settings = AppSettingsDto::from([]);
// timezone: 'UTC', locale: 'en', perPage: 25 (from config)

$settings = AppSettingsDto::from(['timezone' => 'America/New_York']);
// timezone: 'America/New_York' (from input), locale: 'en', perPage: 25
```

### From Environment Variable

```php
// .env
APP_URL=https://example.com
API_KEY=secret123
MAX_UPLOAD_SIZE=10485760

class ApiConfigDto extends Dto
{
    public function __construct(
        #[DefaultFrom(env: 'APP_URL')]
        public readonly string $baseUrl,
        
        #[DefaultFrom(env: 'API_KEY')]
        public readonly string $apiKey,
        
        #[DefaultFrom(env: 'MAX_UPLOAD_SIZE')]
        public readonly int $maxUploadSize,
    ) {}
}

// Usage
$config = ApiConfigDto::from([]);
// Loads defaults from environment variables
```

### From Static Method

```php
class ConfigDto extends Dto
{
    public function __construct(
        #[DefaultFrom(method: 'generateRequestId')]
        public readonly string $requestId,
        
        #[DefaultFrom(method: 'getDefaultTimestamp')]
        public readonly int $timestamp,
    ) {}
    
    protected static function generateRequestId(): string
    {
        return uniqid('req_', true);
    }
    
    protected static function getDefaultTimestamp(): int
    {
        return time();
    }
}

// Usage
$config = ConfigDto::from(['timestamp' => 1234567890]);
// requestId: generated (from method)
// timestamp: 1234567890 (from input, not from method)
```

### Combined Sources

```php
class ApplicationDto extends Dto
{
    public function __construct(
        #[DefaultFrom(config: 'app.name')]
        public readonly string $name,
        
        #[DefaultFrom(env: 'APP_ENV')]
        public readonly string $environment,
        
        #[DefaultFrom(method: 'generateVersion')]
        public readonly string $version,
    ) {}
    
    protected static function generateVersion(): string
    {
        return date('YmdHis');
    }
}
```

---

## Deprecated

**Mark properties as deprecated with migration guidance.**

### Signature

```php
#[Deprecated(
    message: string = 'This property is deprecated',
    since: ?string = null,      // Version when deprecated
    useInstead: ?string = null, // Replacement property
)]
```

### Basic Deprecation

```php
use JOOservices\Dto\Attributes\Deprecated;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        
        #[Deprecated]
        public readonly string $username,  // Deprecated without guidance
    ) {}
}
```

### With Migration Guidance

```php
class ApiResponseDto extends Dto
{
    public function __construct(
        #[Deprecated(
            message: 'Use responseData instead',
            since: '2.0',
            useInstead: 'responseData'
        )]
        public readonly array $data,
        
        public readonly array $responseData,
    ) {}
}
```

### API Version Migration

```php
class ProductDtoV1 extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        
        #[Deprecated(
            message: 'Moved to separate pricing endpoint in v2',
            since: '2.0',
            useInstead: 'Use /api/v2/products/{id}/pricing'
        )]
        public readonly float $price,
    ) {}
}

class ProductDtoV2 extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        // price removed - use dedicated pricing endpoint
    ) {}
}
```

### Deprecation with Trigger Warning

```php
class LegacyDto extends Dto
{
    public function __construct(
        #[Deprecated(
            message: 'This field will be removed in v3.0',
            since: '2.5',
            useInstead: 'newField'
        )]
        public readonly ?string $oldField = null,
        
        public readonly ?string $newField = null,
    ) {}
    
    protected function afterHydration(): void
    {
        // Trigger deprecation warning if old field is used
        if ($this->oldField !== null) {
            trigger_error(
                "Property 'oldField' is deprecated since v2.5. Use 'newField' instead.",
                E_USER_DEPRECATED
            );
        }
    }
}
```

---

## StrictType

**Force strict type checking for specific properties, overriding Context castMode.**

### Signature

```php
#[StrictType(
    message: string = 'Type mismatch'
)]
```

### Basic Usage

```php
use JOOservices\Dto\Attributes\StrictType;

class PaymentDto extends Dto
{
    public function __construct(
        public readonly string $orderId,
        
        #[StrictType]  // Must be exact float, no string conversion
        public readonly float $amount,
        
        #[StrictType]  // Must be exact int
        public readonly int $quantity,
    ) {}
}

// Loose mode context
$context = new Context(castMode: 'loose');

// This works (loose mode)
$payment = PaymentDto::from([
    'order_id' => '123',     // string → string (OK)
    'amount' => '99.99',     // ❌ Throws! StrictType requires exact float
    'quantity' => '5',       // ❌ Throws! StrictType requires exact int
], $context);

// This works
$payment = PaymentDto::from([
    'order_id' => '123',
    'amount' => 99.99,       // ✅ Exact float
    'quantity' => 5,         // ✅ Exact int
], $context);
```

### Financial Data

```php
class InvoiceDto extends Dto
{
    public function __construct(
        public readonly string $invoiceNumber,
        
        #[StrictType(message: 'Amount must be exact float for financial accuracy')]
        public readonly float $subtotal,
        
        #[StrictType(message: 'Tax must be exact float')]
        public readonly float $tax,
        
        #[StrictType(message: 'Total must be exact float')]
        public readonly float $total,
    ) {}
}

// Ensures no precision loss from string conversion
```

### ID Fields

```php
class EntityDto extends Dto
{
    public function __construct(
        #[StrictType(message: 'ID must be exact integer')]
        public readonly int $id,
        
        #[StrictType(message: 'Parent ID must be exact integer or null')]
        public readonly ?int $parentId,
    ) {}
}
```

### Custom Error Messages

```php
class OrderDto extends Dto
{
    public function __construct(
        #[StrictType(message: 'Order total must be provided as float, not string. This ensures financial precision.')]
        public readonly float $total,
        
        #[StrictType(message: 'Item count must be integer. Received: {actual_type}')]
        public readonly int $itemCount,
    ) {}
}
```

---

## OptionalProperty

**Document properties that use Optional<T> wrapper.**

### Signature

```php
#[OptionalProperty]
```

### Basic Usage

```php
use JOOservices\Dto\Core\Optional;
use JOOservices\Dto\Attributes\OptionalProperty;

class UserProfileDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        
        #[OptionalProperty]
        public readonly Optional $bio,  // Optional<string>
        
        #[OptionalProperty]
        public readonly Optional $avatar,  // Optional<string>
    ) {}
}

// Usage
$profile = UserProfileDto::from([
    'id' => 1,
    'name' => 'John',
    // bio and avatar missing
]);

if ($profile->bio->isPresent()) {
    echo $profile->bio->get();
}
```

### API Documentation

```php
/**
 * User registration data.
 */
class RegisterDto extends Dto
{
    public function __construct(
        /** Username (required) */
        public readonly string $username,
        
        /** Email address (required) */
        public readonly string $email,
        
        /** Optional phone number */
        #[OptionalProperty]
        public readonly Optional $phone,
        
        /** Optional referral code */
        #[OptionalProperty]
        public readonly Optional $referralCode,
    ) {}
}
```

---

## Real-World Examples

### Example 1: Configuration Management

```php
class DatabaseConfigDto extends Dto
{
    public function __construct(
        #[DefaultFrom(env: 'DB_HOST')]
        public readonly string $host,
        
        #[DefaultFrom(env: 'DB_PORT')]
        public readonly int $port,
        
        #[DefaultFrom(env: 'DB_DATABASE')]
        public readonly string $database,
        
        #[DefaultFrom(env: 'DB_USERNAME')]
        public readonly string $username,
        
        #[DefaultFrom(env: 'DB_PASSWORD')]
        public readonly string $password,
        
        #[DefaultFrom(config: 'database.connections.mysql.charset')]
        public readonly string $charset,
        
        #[DefaultFrom(config: 'database.connections.mysql.collation')]
        public readonly string $collation,
    ) {}
}

// All defaults loaded from .env and config
$config = DatabaseConfigDto::from([]);
```

### Example 2: API Versioning

```php
class ApiUserDtoV1 extends Dto
{
    public function __construct(
        public readonly int $id,
        
        #[Deprecated(
            message: 'Use email instead',
            since: '1.5',
            useInstead: 'email'
        )]
        public readonly string $mail,
        
        public readonly string $email,
    ) {}
}

class ApiUserDtoV2 extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        // mail field removed
    ) {}
}
```

### Example 3: Financial Application

```php
class TransactionDto extends Dto
{
    public function __construct(
        #[DefaultFrom(method: 'generateTransactionId')]
        public readonly string $transactionId,
        
        #[StrictType(message: 'Amount must be exact float')]
        public readonly float $amount,
        
        #[StrictType(message: 'Fee must be exact float')]
        public readonly float $fee,
        
        #[DefaultFrom(env: 'DEFAULT_CURRENCY')]
        public readonly string $currency,
        
        #[DefaultFrom(method: 'getCurrentTimestamp')]
        public readonly int $timestamp,
    ) {}
    
    protected static function generateTransactionId(): string
    {
        return 'txn_' . uniqid('', true);
    }
    
    protected static function getCurrentTimestamp(): int
    {
        return time();
    }
}
```

### Example 4: Feature Flags

```php
class FeatureFlagsDto extends Dto
{
    public function __construct(
        #[DefaultFrom(env: 'FEATURE_NEW_UI')]
        public readonly bool $newUi,
        
        #[DefaultFrom(env: 'FEATURE_BETA_API')]
        public readonly bool $betaApi,
        
        #[DefaultFrom(config: 'features.analytics')]
        public readonly bool $analytics,
        
        #[Deprecated(
            message: 'Old dashboard removed in v3.0',
            since: '2.8',
            useInstead: 'newUi'
        )]
        #[DefaultFrom(env: 'FEATURE_OLD_DASHBOARD')]
        public readonly bool $oldDashboard,
    ) {}
}
```

### Example 5: Multi-Tenant Configuration

```php
class TenantConfigDto extends Dto
{
    public function __construct(
        public readonly int $tenantId,
        
        #[DefaultFrom(config: 'tenants.default.max_users')]
        public readonly int $maxUsers,
        
        #[DefaultFrom(config: 'tenants.default.storage_gb')]
        public readonly int $storageGb,
        
        #[DefaultFrom(config: 'tenants.default.features')]
        public readonly array $enabledFeatures,
        
        #[OptionalProperty]
        public readonly Optional $customDomain,
    ) {}
}
```

---

## Best Practices

### 1. Security with DefaultFrom

✅ **DO:**
```php
// Use environment variables for secrets
#[DefaultFrom(env: 'API_KEY')]
public readonly string $apiKey;
```

❌ **DON'T:**
```php
// Never hardcode secrets in config
#[DefaultFrom(config: 'api.secret_key')]  // Bad if config is in git
public readonly string $apiKey;
```

### 2. Clear Deprecation Messages

✅ **DO:**
```php
#[Deprecated(
    message: 'Use responseData field instead. The data field will be removed in v3.0',
    since: '2.5',
    useInstead: 'responseData'
)]
```

❌ **DON'T:**
```php
#[Deprecated]  // No guidance for users
```

### 3. StrictType for Critical Data

```php
class FinancialDto extends Dto
{
    // ✅ Always strict for money
    #[StrictType]
    public readonly float $amount;
    
    // ✅ Strict for quantities
    #[StrictType]
    public readonly int $quantity;
}
```

### 4. Document Optional Properties

```php
/**
 * User profile data.
 * 
 * @property Optional<string> $bio Optional biography
 * @property Optional<string> $website Optional website URL
 */
class ProfileDto extends Dto
{
    #[OptionalProperty]
    public readonly Optional $bio;
    
    #[OptionalProperty]
    public readonly Optional $website;
}
```

---

## Summary

- ✅ **#[DefaultFrom]** - Load defaults from config, env, or methods
- ✅ **#[Deprecated]** - Mark deprecated properties with migration guidance
- ✅ **#[StrictType]** - Enforce strict type checking
- ✅ **#[OptionalProperty]** - Document Optional<T> properties
- ✅ Use environment variables for sensitive defaults
- ✅ Provide clear deprecation messages with alternatives
- ✅ Use strict types for financial/critical data

---

**Next:** [Optional & Partial](./optional-partial.md) | [Real-World Scenarios](./real-world-scenarios.md)
