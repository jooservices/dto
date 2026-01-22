# 🔧 Troubleshooting Guide

Common issues and solutions when using **jooservices/dto**.

---

## Installation Issues

### Issue: "Class Not Found"

**Error:**
```
Fatal error: Class 'JOOservices\Dto\Core\Dto' not found
```

**Cause:** Autoloader not loaded or incorrect namespace

**Solution:**
```php
// Make sure autoloader is included
require __DIR__ . '/vendor/autoload.php';

// Use correct namespace
use JOOservices\Dto\Core\Dto;  // ✅ Correct
use Jooservices\Dto\Core\Dto;  // ❌ Wrong case
```

---

### Issue: Composer Install Fails

**Error:**
```
Your requirements could not be resolved to an installable set of packages.
```

**Cause:** PHP version mismatch

**Solution:**
```bash
# Check PHP version
php -v

# Should be PHP 8.5 or higher
# Update if needed:
sudo apt install php8.5  # Ubuntu/Debian
brew install php@8.5     # macOS
```

---

## Creation Issues

### Issue: "Cannot Assign to Readonly Property"

**Error:**
```
Error: Cannot modify readonly property UserDto::$name
```

**Cause:** Trying to modify an immutable DTO

**Solution:**
```php
// ❌ Don't do this
$user = UserDto::from(['name' => 'John']);
$user->name = 'Jane';  // Error!

// ✅ Create a new instance instead
$updatedUser = new UserDto(
    name: 'Jane',
    email: $user->email
);

// ✅ Or use mutable Data objects
use JOOservices\Dto\Core\Data;

class UserData extends Data
{
    public string $name;  // No readonly
}

$user = new UserData();
$user->name = 'John';
$user->name = 'Jane';  // ✅ Works
```

---

### Issue: "Undefined Array Key"

**Error:**
```
Warning: Undefined array key "email" 
```

**Cause:** Missing required property in input data

**Solution:**
```php
// ❌ Missing 'email' field
$data = ['name' => 'John'];
$user = UserDto::from($data);  // Error!

// ✅ Provide all required fields
$data = [
    'name' => 'John',
    'email' => 'john@example.com'
];
$user = UserDto::from($data);

// ✅ Or make property nullable
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $email = null,  // Nullable
    ) {}
}
```

---

### Issue: "Too Few Arguments"

**Error:**
```
ArgumentCountError: Too few arguments to function UserDto::__construct()
```

**Cause:** Missing required constructor parameters

**Solution:**
```php
// ❌ Missing required parameter
$user = new UserDto(name: 'John');  // Missing email

// ✅ Provide all required parameters
$user = new UserDto(
    name: 'John',
    email: 'john@example.com'
);

// ✅ Or add default values
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email = 'unknown@example.com',
    ) {}
}
```

---

## Type Casting Issues

### Issue: Invalid DateTime Format

**Error:**
```
Exception: Failed to parse time string
```

**Cause:** Invalid date format

**Solution:**
```php
// ❌ Invalid format
$data = ['created_at' => 'yesterday'];  // Too vague

// ✅ Use standard formats
$data = [
    'created_at' => '2024-01-20',
    // or '2024-01-20 10:30:00'
    // or '2024-01-20T10:30:00Z'
];

// ✅ Or handle custom formats
$dateString = '20/01/2024';
$date = DateTime::createFromFormat('d/m/Y', $dateString);
$data = ['created_at' => $date->format('Y-m-d')];
```

---

### Issue: Type Mismatch

**Error:**
```
TypeError: UserDto::__construct(): Argument #1 ($id) must be of type int, string given
```

**Cause:** Incorrect data type

**Solution:**
```php
// ❌ String instead of int
$data = ['id' => '123'];  // String
$user = new UserDto(id: $data['id']);  // Error!

// ✅ Cast explicitly
$user = new UserDto(id: (int) $data['id']);

// ✅ Or use from() which auto-casts
$user = UserDto::from($data);  // Handles conversion
```

---

## Nested Object Issues

### Issue: Nested Object Not Created

**Error:**
```
TypeError: Argument must be of type AddressDto, array given
```

**Cause:** Nested data not converted to DTO

**Solution:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}
}

// ❌ Array not converted
$user = new UserDto(
    name: 'John',
    address: ['street' => '123 Main St']  // Still an array!
);

// ✅ Use from() method (auto-converts)
$user = UserDto::from([
    'name' => 'John',
    'address' => [
        'street' => '123 Main St',
        'city' => 'New York'
    ]
]);

// ✅ Or create explicitly
$user = new UserDto(
    name: 'John',
    address: new AddressDto(
        street: '123 Main St',
        city: 'New York'
    )
);
```

---

## Array/Collection Issues

### Issue: Array Type Not Preserved

**Problem:** Array items aren't converted to DTOs

**Solution:**
```php
class OrderDto extends Dto
{
    public function __construct(
        /** @var OrderItemDto[] */  // Add DocBlock
        public readonly array $items,
    ) {}
}

// Use from() to auto-convert array items
$order = OrderDto::from([
    'items' => [
        ['name' => 'Product 1', 'price' => 99.99],
        ['name' => 'Product 2', 'price' => 49.99],
    ]
]);

// Each item is now OrderItemDto
foreach ($order->items as $item) {
    echo $item->name;  // ✅ Works
}
```

---

## Serialization Issues

### Issue: Circular Reference

**Error:**
```
Error: Circular reference detected
```

**Cause:** Objects referencing each other

**Solution:**
```php
// ❌ Circular reference
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly ?CompanyDto $company,
    ) {}
}

class CompanyDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var UserDto[] */
        public readonly array $employees,  // References UserDto again!
    ) {}
}

// ✅ Break the circle
class CompanyDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var int[] User IDs instead */
        public readonly array $employeeIds,
    ) {}
}
```

---

### Issue: Hidden Field Still Appears

**Problem:** Field marked as hidden still in output

**Solution:**
```php
use JOOservices\Dto\Attributes\Hidden;

class UserDto extends Dto
{
    public function __construct(
        public readonly string $email,
        
        #[Hidden]
        public readonly string $password,
    ) {}
}

$user = UserDto::from([
    'email' => 'john@example.com',
    'password' => 'secret'
]);

// Use toArray() not json_encode directly
$array = $user->toArray();  // ✅ Password hidden
$json = json_encode($array);
```

---

## Performance Issues

### Issue: Slow Creation

**Problem:** Creating DTOs is slow

**Solution:**
```php
// ✅ First call caches metadata
$user1 = UserDto::from($data1);  // Slower (analyzes class)

// ✅ Subsequent calls use cache
$user2 = UserDto::from($data2);  // Fast
$user3 = UserDto::from($data3);  // Fast

// ✅ Batch operations
$users = array_map(
    fn($data) => UserDto::from($data),
    $usersData
);
```

---

## Validation Issues

### Issue: No Validation Happening

**Problem:** Invalid data accepted without errors

**Solution:**
```php
// DTOs don't validate by default
// Validate BEFORE creating DTO

function createUser(array $data): UserDto
{
    // ✅ Validate first
    if (empty($data['email'])) {
        throw new ValidationException('Email required');
    }
    
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new ValidationException('Invalid email');
    }
    
    return UserDto::from($data);
}
```

---

## Naming Strategy Issues

### Issue: Property Name Mismatch

**Problem:** API uses snake_case but DTO uses camelCase

**Solution:**
```php
use JOOservices\Dto\Attributes\MapFrom;

class UserDto extends Dto
{
    public function __construct(
        #[MapFrom('first_name')]  // Map from snake_case
        public readonly string $firstName,  // To camelCase
        
        #[MapFrom('last_name')]
        public readonly string $lastName,
    ) {}
}

// API response uses snake_case
$user = UserDto::from([
    'first_name' => 'John',
    'last_name' => 'Doe'
]);

echo $user->firstName;  // ✅ Works
```

---

## Common Mistakes

### Mistake 1: Modifying Immutable DTO

```php
// ❌ Wrong
$user->name = 'New Name';

// ✅ Right - Create new instance
$updatedUser = new UserDto(
    name: 'New Name',
    email: $user->email
);
```

### Mistake 2: Missing Readonly

```php
// ❌ Wrong - Mutable DTO
class UserDto extends Dto
{
    public function __construct(
        public string $name,  // Missing readonly
    ) {}
}

// ✅ Right - Immutable
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {}
}
```

### Mistake 3: Wrong Base Class

```php
// ❌ Wrong
class UserDto
{
    // Not extending anything
}

// ✅ Right
use JOOservices\Dto\Core\Dto;

class UserDto extends Dto
{
    // ...
}
```

### Mistake 4: Missing Type Hints

```php
// ❌ Wrong
public readonly $name;

// ✅ Right
public readonly string $name;
```

---

## Debugging Tips

### Tip 1: Check Input Data

```php
// Before creating DTO
var_dump($data);
print_r($data);

// Then create
$dto = UserDto::from($data);
```

### Tip 2: Use Try-Catch

```php
try {
    $user = UserDto::from($data);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
    echo "\nData: " . print_r($data, true);
}
```

### Tip 3: Check Types

```php
$user = UserDto::from($data);

var_dump(get_class($user));           // Class name
var_dump(get_object_vars($user));     // All properties
var_dump($user instanceof Dto);       // Is it a DTO?
```

### Tip 4: Enable Error Reporting

```php
// Add to top of file
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

---

## Getting Help

### When to Ask for Help

If you've tried the above and still have issues:

1. **GitHub Issues** - For bugs
   - https://github.com/jooservices/dto/issues

2. **GitHub Discussions** - For questions
   - https://github.com/jooservices/dto/discussions

3. **Stack Overflow** - Tag: `jooservices-dto`

### What to Include

When asking for help, include:

- PHP version (`php -v`)
- Library version (`composer show jooservices/dto`)
- Complete error message
- Minimal code example
- Input data structure
- Expected vs actual behavior

**Example:**
```
PHP: 8.5.0
Library: jooservices/dto 1.0.0

Error:
TypeError: Argument must be of type int, string given

Code:
$user = UserDto::from(['id' => '123']);

Expected: Create user with ID 123
Actual: Type error
```

---

## Quick Reference

| Issue | Solution |
|-------|----------|
| Class not found | Check autoloader, namespace |
| Can't modify property | Use Data instead of Dto |
| Undefined array key | Provide required fields or use nullable |
| Type mismatch | Use from() for auto-casting |
| Nested object error | Use from() method |
| Slow performance | Metadata is cached after first use |
| No validation | Validate before creating DTO |
| Name mismatch | Use #[MapFrom] attribute |

---

## Still Stuck?

- 📖 Re-read [Basic Concepts](../01-getting-started/basic-concepts.md)
- 💡 Check [Examples](../03-examples/)
- 📚 Review [Best Practices](./best-practices.md)
- 🔍 Search [GitHub Issues](https://github.com/jooservices/dto/issues)

---

**Remember:** Most issues are solved by:
1. Using `from()` instead of constructor
2. Adding `readonly` to properties
3. Using correct namespace
4. Providing all required data

Good luck! 🚀
