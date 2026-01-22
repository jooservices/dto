# 🔀 Optional<T> & Partial DTOs

Complete guide to Optional<T> wrapper and PartialDtoBuilder in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Optional<T>](#optionalt)
3. [PartialDtoBuilder](#partialdtobuilder)
4. [Real-World Examples](#real-world-examples)
5. [Best Practices](#best-practices)

---

## Introduction

Two powerful features for handling missing or partial data:

| Feature | Purpose | Use Case |
|---------|---------|----------|
| **Optional<T>** | Type-safe wrapper for values that may be absent | API responses, database queries, config |
| **PartialDtoBuilder** | Create DTOs with only specific fields | PATCH requests, partial updates, forms |

### Why Optional<T> vs Nullable?

```php
// ❌ Nullable - easy to forget null checks
public readonly ?string $email;
if ($user->email !== null) {
    sendEmail($user->email);
}

// ✅ Optional<T> - explicit handling required
public readonly Optional $email;  // Optional<string>
$user->email->ifPresent(fn($email) => sendEmail($email));
```

---

## Optional<T>

**Type-safe wrapper preventing null-related bugs.**

### Creating Optional Values

```php
use JOOservices\Dto\Core\Optional;

// With a value
$present = Optional::of('hello');

// Empty (no value)
$empty = Optional::empty();

// From nullable value
$value = null;
$optional = $value !== null ? Optional::of($value) : Optional::empty();
```

### Checking Presence

```php
$optional = Optional::of(42);

if ($optional->isPresent()) {
    echo "Has value!";
}

if ($optional->isEmpty()) {
    echo "No value";
}
```

### Getting Values Safely

#### get() - Throw if Empty

```php
$optional = Optional::of('value');
$value = $optional->get();  // "value"

$empty = Optional::empty();
$value = $empty->get();  // ❌ Throws RuntimeException
```

#### orElse() - Provide Default

```php
$optional = Optional::empty();
$value = $optional->orElse('default');  // "default"

$optional = Optional::of('actual');
$value = $optional->orElse('default');  // "actual"
```

#### orElseGet() - Lazy Default

```php
$optional = Optional::empty();

// Expensive operation only called if empty
$value = $optional->orElseGet(function() {
    return expensiveComputation();
});
```

#### orElseThrow() - Custom Exception

```php
$optional = Optional::empty();

$value = $optional->orElseThrow(function() {
    return new \DomainException('User not found');
});
// Throws DomainException
```

### Conditional Execution

#### ifPresent() - Execute if Has Value

```php
$optional = Optional::of('user@example.com');

$optional->ifPresent(function($email) {
    sendWelcomeEmail($email);
    logEvent("Email sent to: $email");
});
```

#### ifEmpty() - Execute if No Value

```php
$optional = Optional::empty();

$optional->ifEmpty(function() {
    logWarning('No email address provided');
});
```

### Transformations

#### map() - Transform Value

```php
$optional = Optional::of('  hello  ');

$trimmed = $optional->map(fn($str) => trim($str));
// Optional::of('hello')

$upper = $trimmed->map(fn($str) => strtoupper($str));
// Optional::of('HELLO')

// Chain transformations
$result = Optional::of(5)
    ->map(fn($n) => $n * 2)     // 10
    ->map(fn($n) => $n + 3)     // 13
    ->get();                     // 13
```

#### filter() - Conditional Keep

```php
$optional = Optional::of(42);

$even = $optional->filter(fn($n) => $n % 2 === 0);
// Still Optional::of(42)

$odd = $optional->filter(fn($n) => $n % 2 !== 0);
// Optional::empty()
```

### Using with DTOs

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\Optional;

class UserProfileDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly Optional $bio,        // Optional<string>
        public readonly Optional $avatar,     // Optional<string>
        public readonly Optional $website,    // Optional<string>
    ) {}
}

// Create with missing optional fields
$profile = UserProfileDto::from([
    'id' => 1,
    'username' => 'john_doe',
    // bio, avatar, website are missing
]);

// Safe access
$bioText = $profile->bio->orElse('No bio provided');

$profile->website->ifPresent(function($url) {
    echo "Website: $url";
});

// Chain operations
$displayName = $profile->bio
    ->map(fn($bio) => substr($bio, 0, 50))
    ->map(fn($bio) => $bio . '...')
    ->orElse('Software Developer');
```

---

## PartialDtoBuilder

**Create DTOs with only specific fields populated.**

### Basic Usage

```php
use JOOservices\Dto\Core\PartialDtoBuilder;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
    ) {}
}

// Only hydrate specific fields
$builder = new PartialDtoBuilder(
    dtoClass: UserDto::class,
    allowedFields: ['name', 'email']
);

$partial = $builder->from([
    'id' => 1,           // Ignored
    'name' => 'John',     // ✅ Included
    'email' => 'john@example.com',  // ✅ Included
    'phone' => '555-0123',  // Ignored
]);

// Only name and email are hydrated
```

### PATCH Endpoint

```php
class UserController
{
    public function update(int $id, Request $request): Response
    {
        // Only allow updating specific fields
        $builder = new PartialDtoBuilder(
            dtoClass: UpdateUserDto::class,
            allowedFields: ['name', 'email', 'bio']
        );
        
        $updateData = $builder->from($request->all());
        
        $this->userService->update($id, $updateData);
        
        return response()->json(['message' => 'Updated']);
    }
}
```

### Form Handling

```php
class ProductFormHandler
{
    public function handlePartialSubmit(array $formData): ProductDto
    {
        // User might submit only some fields
        $submittedFields = array_keys($formData);
        
        $builder = new PartialDtoBuilder(
            dtoClass: ProductDto::class,
            allowedFields: $submittedFields
        );
        
        return $builder->from($formData);
    }
}
```

### Multi-Step Forms

```php
class RegistrationWizard
{
    private array $collectedData = [];
    
    public function step1(array $basicInfo): void
    {
        $builder = new PartialDtoBuilder(
            dtoClass: UserRegistrationDto::class,
            allowedFields: ['name', 'email']
        );
        
        $dto = $builder->from($basicInfo);
        $this->collectedData = array_merge($this->collectedData, $dto->toArray());
    }
    
    public function step2(array $profileInfo): void
    {
        $builder = new PartialDtoBuilder(
            dtoClass: UserRegistrationDto::class,
            allowedFields: ['bio', 'avatar', 'location']
        );
        
        $dto = $builder->from($profileInfo);
        $this->collectedData = array_merge($this->collectedData, $dto->toArray());
    }
    
    public function complete(): UserRegistrationDto
    {
        // Create complete DTO from all collected data
        return UserRegistrationDto::from($this->collectedData);
    }
}
```

---

## Real-World Examples

### Example 1: API Response with Optional Fields

```php
class ApiUserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly Optional $phoneNumber,
        public readonly Optional $avatar,
        public readonly Optional $bio,
    ) {}
}

// From API response
$response = $apiClient->get('/users/123');

$user = ApiUserDto::from($response);

// Display phone only if present
$user->phoneNumber->ifPresent(fn($phone) => 
    echo "Contact: $phone"
);

// Avatar with fallback
$avatarUrl = $user->avatar->orElse('/images/default-avatar.png');

// Bio with length limit
$shortBio = $user->bio
    ->map(fn($text) => substr($text, 0, 100))
    ->orElse('No bio available');
```

### Example 2: Database Query Results

```php
class UserRepository
{
    public function findById(int $id): Optional
    {
        $result = $this->db->query(
            'SELECT * FROM users WHERE id = ?',
            [$id]
        )->fetch();
        
        if (!$result) {
            return Optional::empty();
        }
        
        return Optional::of(UserDto::from($result));
    }
}

// Usage
$userOptional = $repo->findById(123);

$user = $userOptional->orElseThrow(fn() => 
    new NotFoundException('User not found')
);

// Or with default
$user = $userOptional->orElseGet(fn() => 
    UserDto::guest()
);
```

### Example 3: Configuration with Optionals

```php
class AppConfigDto extends Dto
{
    public function __construct(
        public readonly string $appName,
        public readonly string $appUrl,
        public readonly Optional $smtpHost,
        public readonly Optional $smtpPort,
        public readonly Optional $smtpUsername,
        public readonly Optional $smtpPassword,
    ) {}
    
    public function hasEmailConfigured(): bool
    {
        return $this->smtpHost->isPresent() 
            && $this->smtpUsername->isPresent();
    }
    
    public function getMailConfig(): ?array
    {
        if (!$this->hasEmailConfigured()) {
            return null;
        }
        
        return [
            'host' => $this->smtpHost->get(),
            'port' => $this->smtpPort->orElse(587),
            'username' => $this->smtpUsername->get(),
            'password' => $this->smtpPassword->get(),
        ];
    }
}
```

### Example 4: PATCH Request Handler

```php
class ArticleController
{
    public function patch(int $id, Request $request): Response
    {
        // Fetch existing article
        $article = $this->articleRepo->find($id);
        
        // Only update provided fields
        $builder = new PartialDtoBuilder(
            dtoClass: ArticleUpdateDto::class,
            allowedFields: array_keys($request->all())
        );
        
        $updates = $builder->from($request->all());
        
        // Merge with existing data
        $updated = $article->merge($updates);
        
        $this->articleRepo->save($updated);
        
        return response()->json($updated);
    }
}
```

### Example 5: Caching with Optional

```php
class CachedUserRepository
{
    public function findByEmail(string $email): Optional
    {
        // Try cache first
        $cacheKey = "user:email:$email";
        
        return Optional::ofNullable($this->cache->get($cacheKey))
            ->orElseGet(function() use ($email, $cacheKey) {
                // Query database
                $user = $this->db->findByEmail($email);
                
                if ($user) {
                    $this->cache->set($cacheKey, $user, ttl: 3600);
                    return Optional::of($user);
                }
                
                return Optional::empty();
            });
    }
}
```

### Example 6: Search with Optional Results

```php
class SearchService
{
    public function search(string $query): Optional
    {
        $results = $this->elasticsearch->search([
            'query' => ['match' => ['content' => $query]]
        ]);
        
        if (empty($results['hits']['hits'])) {
            return Optional::empty();
        }
        
        return Optional::of(
            array_map(fn($hit) => SearchResultDto::from($hit['_source']), 
            $results['hits']['hits'])
        );
    }
}

// Usage
$results = $searchService->search('php dto')
    ->map(fn($hits) => array_slice($hits, 0, 10))
    ->orElse([]);
```

### Example 7: Feature Flags

```php
class FeatureFlagService
{
    public function getFlag(string $name): Optional
    {
        $flag = $this->configRepo->findFlag($name);
        
        return $flag !== null 
            ? Optional::of($flag) 
            : Optional::empty();
    }
}

// Usage
$betaFeatures = $flags->getFlag('beta_ui')
    ->filter(fn($flag) => $flag->isEnabled())
    ->map(fn($flag) => $flag->getConfig())
    ->orElse(['enabled' => false]);
```

### Example 8: Bulk Updates with Partial

```php
class BulkUpdateHandler
{
    public function bulkUpdate(array $updates): array
    {
        $results = [];
        
        foreach ($updates as $update) {
            $builder = new PartialDtoBuilder(
                dtoClass: ProductDto::class,
                allowedFields: array_keys($update['data'])
            );
            
            $partial = $builder->from($update['data']);
            
            $results[] = $this->productRepo->update(
                $update['id'],
                $partial
            );
        }
        
        return $results;
    }
}
```

---

## Best Practices

### 1. Use Optional for Truly Optional Data

✅ **DO:**
```php
class UserDto extends Dto
{
    public readonly Optional $bio;         // May or may not exist
    public readonly Optional $website;     // May or may not exist
}
```

❌ **DON'T:**
```php
class UserDto extends Dto
{
    public readonly Optional $id;          // ID should always exist
    public readonly Optional $email;       // Email is required
}
```

### 2. Chain Operations

```php
// ✅ Clean chaining
$displayText = $user->bio
    ->map(fn($text) => trim($text))
    ->filter(fn($text) => strlen($text) > 0)
    ->map(fn($text) => substr($text, 0, 100))
    ->orElse('No bio');

// ❌ Manual null checks
if ($user->bio !== null) {
    $text = trim($user->bio);
    if (strlen($text) > 0) {
        $displayText = substr($text, 0, 100);
    } else {
        $displayText = 'No bio';
    }
} else {
    $displayText = 'No bio';
}
```

### 3. Use orElseGet for Expensive Operations

```php
// ✅ Lazy evaluation
$result = $optional->orElseGet(fn() => expensiveOperation());

// ❌ Eager evaluation
$result = $optional->orElse(expensiveOperation());  // Always called!
```

### 4. PartialDtoBuilder for PATCH

```php
// ✅ Only update provided fields
$builder = new PartialDtoBuilder(
    UserDto::class,
    array_keys($request->all())
);
```

### 5. Document Optional Properties

```php
/**
 * @property Optional<string> $bio User biography (optional)
 * @property Optional<string> $avatar Avatar URL (optional)
 */
class UserDto extends Dto
{
    public readonly Optional $bio;
    public readonly Optional $avatar;
}
```

---

## Performance Tips

### 1. Avoid Excessive Optional Creation

```php
// ❌ Creating many Optional instances in loop
foreach ($users as $user) {
    $email = Optional::of($user->email)->orElse('');
}

// ✅ Simple null check in loop
foreach ($users as $user) {
    $email = $user->email ?? '';
}
```

### 2. Use PartialDtoBuilder Wisely

```php
// ✅ Reuse builder for similar operations
$builder = new PartialDtoBuilder(UserDto::class, ['name', 'email']);
foreach ($updates as $update) {
    $dto = $builder->from($update);
}
```

---

## Summary

- ✅ **Optional<T>** - Type-safe wrapper for missing values
- ✅ **map()**, **filter()** - Chain transformations
- ✅ **orElse()**, **orElseGet()** - Provide defaults safely
- ✅ **ifPresent()**, **ifEmpty()** - Conditional execution
- ✅ **PartialDtoBuilder** - Create DTOs with specific fields
- ✅ Use for PATCH endpoints, multi-step forms, bulk updates
- ✅ Better than nullable properties for clarity and safety

---

**Next:** [Schema Generation](./schema-generation.md) | [Advanced Attributes](./advanced-attributes.md)
