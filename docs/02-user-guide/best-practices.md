# ✨ Best Practices

Expert tips for using **jooservices/dto** effectively.

---

## General Principles

### 1. Keep DTOs Simple and Focused

✅ **DO:**
```php
// Single responsibility - user basic info
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}
```

❌ **DON'T:**
```php
// Too many responsibilities
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly array $orders,
        public readonly array $payments,
        public readonly array $preferences,
        public readonly array $activityLog,
    ) {}
}
```

---

### 2. Use Immutability for DTOs

✅ **DO:**
```php
class ProductDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
    ) {}
}
```

❌ **DON'T:**
```php
class ProductDto extends Dto
{
    public function __construct(
        public string $name,     // Missing readonly!
        public float $price,     // Missing readonly!
    ) {}
}
```

---

### 3. Use Descriptive Names

✅ **DO:**
```php
class UserRegistrationDto extends Dto
{
    public function __construct(
        public readonly string $emailAddress,
        public readonly string $firstName,
        public readonly string $lastName,
    ) {}
}
```

❌ **DON'T:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly string $em,   // Unclear
        public readonly string $fn,   // Unclear
        public readonly string $ln,   // Unclear
    ) {}
}
```

---

## Naming Conventions

### Property Names

✅ **Use camelCase:**
```php
public readonly string $firstName;
public readonly DateTime $createdAt;
public readonly bool $isActive;
```

❌ **Avoid:**
```php
public readonly string $first_name;   // snake_case
public readonly DateTime $CreatedAt;  // PascalCase
public readonly bool $is_active;      // snake_case
```

### Class Names

✅ **Be specific and descriptive:**
```php
CreateUserRequestDto
UserResponseDto
UpdateProductRequestDto
OrderSummaryDto
```

❌ **Too generic:**
```php
UserDto          // Which use case?
RequestDto       // Request for what?
DataDto          // What data?
```

---

## Type Safety

### Always Use Type Hints

✅ **DO:**
```php
class OrderDto extends Dto
{
    public function __construct(
        public readonly string $id,
        public readonly float $total,
        public readonly DateTime $createdAt,
        public readonly bool $isPaid,
    ) {}
}
```

❌ **DON'T:**
```php
class OrderDto extends Dto
{
    public function __construct(
        public readonly $id,         // No type!
        public readonly $total,      // No type!
        public readonly $createdAt,  // No type!
    ) {}
}
```

### Use Nullable Types Appropriately

✅ **DO:**
```php
class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $nickname = null,  // Optional
        public readonly ?DateTime $lastLogin = null,  // Optional
    ) {}
}
```

### Document Array Types

✅ **DO:**
```php
class OrderDto extends Dto
{
    public function __construct(
        /** @var OrderItemDto[] */
        public readonly array $items,
        /** @var string[] */
        public readonly array $tags,
    ) {}
}
```

---

## Composition Over Inheritance

### Use Nested DTOs

✅ **DO:**
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
        public readonly AddressDto $address,  // Composition
    ) {}
}
```

❌ **DON'T:**
```php
class UserWithAddressDto extends AddressDto
{
    public function __construct(
        string $street,
        string $city,
        public readonly string $name,
    ) {
        parent::__construct($street, $city);
    }
}
```

---

## Validation

### Validate at Boundaries

✅ **DO:**
```php
// Validate when receiving external data
$data = $_POST;

if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    throw new ValidationException('Invalid email');
}

$user = UserDto::from($data);  // Clean, validated data
```

❌ **DON'T:**
```php
// Don't put validation in DTO
class UserDto extends Dto
{
    public function __construct(
        public readonly string $email,
    ) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email');
        }
    }
}
```

---

## API Integration

### Separate Request/Response DTOs

✅ **DO:**
```php
class CreateUserRequestDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $password,  // Only in request
    ) {}
}

class UserResponseDto extends Dto
{
    public function __construct(
        public readonly int $id,           // Only in response
        public readonly string $name,
        public readonly string $email,
        public readonly DateTime $createdAt,  // Only in response
    ) {}
}
```

### Handle API Errors Gracefully

✅ **DO:**
```php
class ApiResponseDto extends Dto
{
    public function __construct(
        public readonly bool $success,
        public readonly mixed $data,
        public readonly ?ApiErrorDto $error = null,
    ) {}
}

$response = ApiResponseDto::from($json);

if (!$response->success) {
    // Handle error
    throw new ApiException($response->error->message);
}
```

---

## Performance

### Use Metadata Caching

The library automatically caches class metadata. To optimize:

✅ **DO:**
```php
// First call analyzes class (slower)
$user1 = UserDto::from($data1);

// Subsequent calls use cache (fast)
$user2 = UserDto::from($data2);
$user3 = UserDto::from($data3);
```

### Reuse DTO Instances When Possible

✅ **DO:**
```php
// Parse once
$config = ConfigDto::from($configArray);

// Reuse everywhere
function processOrder(ConfigDto $config) {
    // Use $config
}
```

❌ **DON'T:**
```php
// Parsing in every function call
function processOrder() {
    $config = ConfigDto::from(loadConfig());  // Wasteful
}
```

---

## Testing

### Test DTO Creation

✅ **DO:**
```php
class UserDtoTest extends TestCase
{
    public function test_creates_from_array(): void
    {
        $user = UserDto::from([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }
    
    public function test_converts_to_array(): void
    {
        $user = new UserDto(
            name: 'John Doe',
            email: 'john@example.com'
        );
        
        $array = $user->toArray();
        
        $this->assertIsArray($array);
        $this->assertEquals('John Doe', $array['name']);
    }
}
```

### Test Type Casting

✅ **DO:**
```php
public function test_casts_types_correctly(): void
{
    $product = ProductDto::from([
        'price' => '99.99',        // String
        'stock' => '50',           // String
        'active' => 1,             // Int
    ]);
    
    $this->assertIsFloat($product->price);
    $this->assertIsInt($product->stock);
    $this->assertIsBool($product->active);
}
```

---

## Documentation

### Document Complex Types

✅ **DO:**
```php
/**
 * User profile with orders
 */
class UserProfileDto extends Dto
{
    public function __construct(
        /** User's unique identifier */
        public readonly int $id,
        
        /** User's full name */
        public readonly string $name,
        
        /** @var OrderDto[] List of user orders */
        public readonly array $orders,
    ) {}
}
```

### Add Examples in PHPDoc

✅ **DO:**
```php
/**
 * Money value object
 * 
 * @example
 * $price = Money::from(['amount' => 99.99, 'currency' => 'USD']);
 * echo $price->format(); // "$99.99"
 */
class Money extends Dto
{
    public function __construct(
        public readonly float $amount,
        public readonly string $currency,
    ) {}
}
```

---

## Error Handling

### Provide Context in Exceptions

✅ **DO:**
```php
try {
    $user = UserDto::from($data);
} catch (HydrationException $e) {
    throw new ApplicationException(
        "Failed to create user from data",
        previous: $e
    );
}
```

### Validate Before Creating DTOs

✅ **DO:**
```php
function createUser(array $data): UserDto
{
    $required = ['name', 'email'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new ValidationException("Missing required field: {$field}");
        }
    }
    
    return UserDto::from($data);
}
```

---

## Code Organization

### Group Related DTOs

```
src/Dto/
├── User/
│   ├── UserDto.php
│   ├── CreateUserRequestDto.php
│   ├── UpdateUserRequestDto.php
│   └── UserResponseDto.php
├── Order/
│   ├── OrderDto.php
│   ├── OrderItemDto.php
│   └── OrderSummaryDto.php
└── Common/
    ├── AddressDto.php
    ├── MoneyDto.php
    └── PaginationDto.php
```

---

## Common Patterns

### Pattern 1: Builder for Complex DTOs

```php
class CreateOrderRequestDto extends Dto
{
    public function __construct(
        public readonly string $customerId,
        /** @var OrderItemDto[] */
        public readonly array $items,
        public readonly ?string $couponCode = null,
    ) {}
    
    public static function builder(): CreateOrderRequestDtoBuilder
    {
        return new CreateOrderRequestDtoBuilder();
    }
}

class CreateOrderRequestDtoBuilder
{
    private string $customerId;
    private array $items = [];
    private ?string $couponCode = null;
    
    public function customerId(string $id): self
    {
        $this->customerId = $id;
        return $this;
    }
    
    public function addItem(OrderItemDto $item): self
    {
        $this->items[] = $item;
        return $this;
    }
    
    public function couponCode(string $code): self
    {
        $this->couponCode = $code;
        return $this;
    }
    
    public function build(): CreateOrderRequestDto
    {
        return new CreateOrderRequestDto(
            $this->customerId,
            $this->items,
            $this->couponCode
        );
    }
}

// Usage
$order = CreateOrderRequestDto::builder()
    ->customerId('CUST-123')
    ->addItem($item1)
    ->addItem($item2)
    ->couponCode('SAVE10')
    ->build();
```

### Pattern 2: Factory Methods

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
    
    public static function fromDatabase(\stdClass $row): self
    {
        return new self(
            id: (int) $row->id,
            name: $row->name,
            email: $row->email
        );
    }
    
    public static function fromApiResponse(array $data): self
    {
        return self::from($data);
    }
}
```

---

## Anti-Patterns to Avoid

### ❌ Business Logic in DTOs

```php
// DON'T
class OrderDto extends Dto
{
    public function calculateTax(): float
    {
        // Complex tax calculation doesn't belong here
        return $this->total * 0.08;
    }
}

// DO - Keep DTOs as data containers
class OrderDto extends Dto
{
    public function __construct(
        public readonly float $total,
        public readonly float $tax,  // Pre-calculated
    ) {}
}
```

### ❌ Mutable Properties in DTOs

```php
// DON'T
class UserDto extends Dto
{
    public string $email;  // Mutable!
}

// DO
class UserDto extends Dto
{
    public readonly string $email;  // Immutable
}
```

### ❌ God Objects

```php
// DON'T
class ApplicationDto extends Dto
{
    public function __construct(
        public readonly UserDto $user,
        public readonly array $settings,
        public readonly array $permissions,
        // ... 20 more properties
    ) {}
}

// DO - Split into focused DTOs
class UserContextDto extends Dto { /* ... */ }
class ApplicationSettingsDto extends Dto { /* ... */ }
class PermissionsDto extends Dto { /* ... */ }
```

---

## Checklist

Before committing your DTO:

- [ ] Extends `Dto` base class
- [ ] Uses `public readonly` properties
- [ ] Has proper type hints
- [ ] Uses descriptive names
- [ ] Documents array types with `@var`
- [ ] Follows single responsibility
- [ ] No business logic
- [ ] No mutable properties (unless Data)
- [ ] Tested with unit tests
- [ ] Documented with PHPDoc

---

## Summary

**Key Takeaways:**
1. 🔒 Keep DTOs immutable (use `readonly`)
2. 📝 Use descriptive, consistent naming
3. 🎯 Single responsibility per DTO
4. 🏗️ Compose with nested DTOs
5. ✅ Validate at boundaries
6. 📚 Document complex types
7. 🧪 Write tests for DTOs
8. ⚡ Reuse instances when possible

---

## Next Steps

- 🔍 [Type Casting](./type-casting.md) - Learn about type conversion
- ✅ [Validation](./validation.md) - Add validation rules
- 🐛 [Troubleshooting](./troubleshooting.md) - Common issues
- 💡 [Examples](../03-examples/) - Real-world scenarios

---

**Questions?** [Ask on GitHub](https://github.com/jooservices/dto/discussions)
