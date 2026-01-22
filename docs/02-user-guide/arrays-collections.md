# 📚 Arrays & Collections

Complete guide to working with arrays and collections in **jooservices/dto**.

---

## Table of Contents
1. [Introduction](#introduction)
2. [Simple Arrays](#simple-arrays)
3. [Typed Arrays of DTOs](#typed-arrays-of-dtos)
4. [DataCollection](#datacollection)
5. [PaginatedCollection](#paginatedcollection)
6. [Collection Methods](#collection-methods)
7. [Best Practices](#best-practices)
8. [Common Patterns](#common-patterns)
9. [Troubleshooting](#troubleshooting)

---

## Introduction

The library provides powerful tools for working with arrays and collections of DTOs:

- **Simple Arrays** - Basic PHP arrays with type casting
- **DataCollection** - Immutable collection wrapper for DTOs
- **PaginatedCollection** - Collection with pagination metadata

```php
use JOOservices\Dto\Collections\DataCollection;

$users = new DataCollection(
    UserDto::class,
    $arrayOfUserData
);

foreach ($users as $user) {
    echo $user->name;
}
```

---

## Simple Arrays

### Scalar Arrays

```php
class PostDto extends Dto
{
    public function __construct(
        public readonly string $title,
        /** @var string[] */
        public readonly array $tags,
        /** @var int[] */
        public readonly array $viewCounts,
    ) {}
}

$post = PostDto::from([
    'title' => 'My Post',
    'tags' => ['php', 'dto', 'tutorial'],
    'view_counts' => [100, 250, 400],
]);
```

### Associative Arrays

```php
class ConfigDto extends Dto
{
    public function __construct(
        /** @var array<string, mixed> */
        public readonly array $settings,
    ) {}
}

$config = ConfigDto::from([
    'settings' => [
        'debug' => true,
        'timeout' => 30,
        'url' => 'https://api.example.com',
    ],
]);
```

---

## Typed Arrays of DTOs

### Basic Typed Array

```php
class TagDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
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
    'title' => 'PHP Best Practices',
    'tags' => [
        ['name' => 'PHP', 'slug' => 'php'],
        ['name' => 'Development', 'slug' => 'development'],
    ],
]);

// Access typed array:
foreach ($article->tags as $tag) {
    echo $tag->name;  // TagDto instance
    echo $tag->slug;
}
```

### Nested Arrays

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}

class CompanyDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var AddressDto[] */
        public readonly array $offices,
    ) {}
}

$company = CompanyDto::from([
    'name' => 'Acme Corp',
    'offices' => [
        ['street' => '123 Main St', 'city' => 'New York'],
        ['street' => '456 Oak Ave', 'city' => 'San Francisco'],
        ['street' => '789 Elm Rd', 'city' => 'London'],
    ],
]);
```

### Associative Arrays of DTOs

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}

class TeamDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var array<string, UserDto> */
        public readonly array $members,
    ) {}
}

$team = TeamDto::from([
    'name' => 'Development Team',
    'members' => [
        'lead' => ['id' => 1, 'name' => 'Alice'],
        'backend' => ['id' => 2, 'name' => 'Bob'],
        'frontend' => ['id' => 3, 'name' => 'Charlie'],
    ],
]);

echo $team->members['lead']->name;  // "Alice"
```

---

## DataCollection

Immutable collection wrapper for DTOs with helper methods.

### Creating Collections

```php
use JOOservices\Dto\Collections\DataCollection;

// From array:
$users = new DataCollection(
    UserDto::class,
    [
        ['id' => 1, 'name' => 'Alice'],
        ['id' => 2, 'name' => 'Bob'],
        ['id' => 3, 'name' => 'Charlie'],
    ]
);

// From existing DTOs:
$users = new DataCollection(
    UserDto::class,
    [
        UserDto::from(['id' => 1, 'name' => 'Alice']),
        UserDto::from(['id' => 2, 'name' => 'Bob']),
    ]
);
```

### Using Collections

```php
// Count:
echo $users->count();  // 3

// Iterate:
foreach ($users as $user) {
    echo $user->name;
}

// Check if empty:
if ($users->isEmpty()) {
    echo "No users";
}

// Get first/last:
$first = $users->first();  // UserDto | null
$last = $users->last();    // UserDto | null

// Get all as array:
$allUsers = $users->all();  // UserDto[]
```

### Converting Collections

```php
// To array:
$array = $users->toArray();
// [
//     ['id' => 1, 'name' => 'Alice'],
//     ['id' => 2, 'name' => 'Bob'],
//     ['id' => 3, 'name' => 'Charlie'],
// ]

// To JSON:
$json = $users->toJson();
// '[{"id":1,"name":"Alice"},{"id":2,"name":"Bob"},{"id":3,"name":"Charlie"}]'

$prettyJson = $users->toJson(JSON_PRETTY_PRINT);
```

### Wrapped Collections

Wrap collection output with a key:

```php
$users = new DataCollection(UserDto::class, $data);

// Without wrap:
$users->toArray();
// [
//     ['id' => 1, 'name' => 'Alice'],
//     ['id' => 2, 'name' => 'Bob'],
// ]

// With wrap:
$wrapped = $users->wrap('users');
$wrapped->toArray();
// [
//     'users' => [
//         ['id' => 1, 'name' => 'Alice'],
//         ['id' => 2, 'name' => 'Bob'],
//     ]
// ]
```

---

## PaginatedCollection

Collection with pagination metadata for APIs.

### Creating Paginated Collections

```php
use JOOservices\Dto\Collections\PaginatedCollection;

$users = new PaginatedCollection(
    dtoClass: UserDto::class,
    items: $userDataArray,
    meta: [
        'current_page' => 1,
        'per_page' => 10,
        'total' => 50,
        'last_page' => 5,
    ],
    links: [
        'first' => 'https://api.example.com/users?page=1',
        'last' => 'https://api.example.com/users?page=5',
        'prev' => null,
        'next' => 'https://api.example.com/users?page=2',
    ]
);
```

### From Laravel Paginator

```php
// From Laravel LengthAwarePaginator:
$paginator = User::paginate(10);

$users = PaginatedCollection::fromPaginator(
    UserDto::class,
    $paginator
);

// Automatically extracts:
// - items
// - current_page, per_page, total, last_page
// - prev/next URLs
```

### Using Paginated Collections

```php
// All DataCollection methods work:
echo $users->count();
foreach ($users as $user) {
    echo $user->name;
}

// Access pagination metadata:
$meta = $users->getMeta();
$links = $users->getLinks();

$currentPage = $users->getCurrentPage();  // 1
$perPage = $users->getPerPage();          // 10
$total = $users->getTotal();              // 50
$lastPage = $users->getLastPage();        // 5
```

### Converting Paginated Collections

```php
$array = $users->toArray();
// [
//     'data' => [
//         ['id' => 1, 'name' => 'Alice'],
//         ['id' => 2, 'name' => 'Bob'],
//     ],
//     'meta' => [
//         'current_page' => 1,
//         'per_page' => 10,
//         'total' => 50,
//         'last_page' => 5,
//     ],
//     'links' => [
//         'first' => '...',
//         'last' => '...',
//         'prev' => null,
//         'next' => '...',
//     ]
// ]

// With custom wrap key:
$wrapped = $users->wrap('users');
$wrapped->toArray();
// [
//     'users' => [...],
//     'meta' => {...},
//     'links' => {...}
// ]
```

---

## Collection Methods

### Common Operations

```php
$collection = new DataCollection(UserDto::class, $data);

// Count
$count = $collection->count();

// Check empty
$isEmpty = $collection->isEmpty();

// First item
$first = $collection->first();  // UserDto | null

// Last item
$last = $collection->last();    // UserDto | null

// All items
$all = $collection->all();      // UserDto[]

// Iterate
foreach ($collection as $key => $item) {
    // $key is int|string
    // $item is UserDto
}
```

### Serialization

```php
// To array
$array = $collection->toArray();

// To JSON
$json = $collection->toJson();
$prettyJson = $collection->toJson(JSON_PRETTY_PRINT);

// JSON Serializable
json_encode($collection);  // Works automatically
```

### Immutable Modifications

```php
// Wrap with key
$wrapped = $collection->wrap('users');

// Add context
$context = new Context(/* ... */);
$withContext = $collection->withContext($context);
```

---

## Best Practices

### 1. Document Array Types

✅ **DO:**
```php
class PostDto extends Dto
{
    public function __construct(
        /** @var TagDto[] */
        public readonly array $tags,
    ) {}
}
```

❌ **DON'T:**
```php
class PostDto extends Dto
{
    public function __construct(
        public readonly array $tags,  // No type info
    ) {}
}
```

---

### 2. Use Collections for Large Datasets

✅ **DO:**
```php
use JOOservices\Dto\Collections\DataCollection;

class ApiResponse
{
    public function getUsers(): DataCollection
    {
        return new DataCollection(UserDto::class, $this->userData);
    }
}
```

❌ **DON'T:**
```php
class ApiResponse
{
    public function getUsers(): array  // Less type-safe
    {
        return array_map(
            fn($data) => UserDto::from($data),
            $this->userData
        );
    }
}
```

---

### 3. Use PaginatedCollection for APIs

✅ **DO:**
```php
class UserController
{
    public function index(): JsonResponse
    {
        $paginator = User::paginate(10);
        
        $collection = PaginatedCollection::fromPaginator(
            UserDto::class,
            $paginator
        );
        
        return response()->json($collection->toArray());
    }
}
```

---

### 4. Keep Collections Immutable

✅ **DO:**
```php
$original = new DataCollection(UserDto::class, $data);
$wrapped = $original->wrap('users');  // New instance

// $original is unchanged
```

❌ **DON'T:** Try to modify collection in place
```php
$collection->items[] = $newUser;  // Won't work, readonly
```

---

## Common Patterns

### 1. API Response with Pagination

```php
class UserListResponse
{
    public function __construct(
        private PaginatedCollection $users
    ) {}

    public function toArray(): array
    {
        return $this->users->wrap('users')->toArray();
    }
}

// Usage:
$users = User::paginate(10);
$response = new UserListResponse(
    PaginatedCollection::fromPaginator(UserDto::class, $users)
);

return response()->json($response->toArray());
// {
//     "users": [...],
//     "meta": {...},
//     "links": {...}
// }
```

---

### 2. Transform Collection

```php
$users = new DataCollection(UserDto::class, $userData);

// Transform to different format:
$names = array_map(
    fn($user) => $user->name,
    $users->all()
);

// Filter:
$activeUsers = array_filter(
    $users->all(),
    fn($user) => $user->status === Status::ACTIVE
);

// Create new collection:
$activeCollection = new DataCollection(
    UserDto::class,
    $activeUsers
);
```

---

### 3. Nested Collections

```php
class DepartmentDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var EmployeeDto[] */
        public readonly array $employees,
    ) {}
}

class CompanyDto extends Dto
{
    public function __construct(
        public readonly string $name,
        /** @var DepartmentDto[] */
        public readonly array $departments,
    ) {}
}

$company = CompanyDto::from([
    'name' => 'Acme Corp',
    'departments' => [
        [
            'name' => 'Engineering',
            'employees' => [
                ['name' => 'Alice', 'role' => 'Developer'],
                ['name' => 'Bob', 'role' => 'Architect'],
            ],
        ],
        [
            'name' => 'Sales',
            'employees' => [
                ['name' => 'Charlie', 'role' => 'Manager'],
            ],
        ],
    ],
]);

// Access nested arrays:
foreach ($company->departments as $dept) {
    echo $dept->name . "\n";
    foreach ($dept->employees as $emp) {
        echo "  - " . $emp->name . "\n";
    }
}
```

---

## Troubleshooting

### Issue: Array Items Not Casting to DTOs

**Problem:**
```php
public readonly array $users;  // No type documentation
```

**Solution:** Add PHPDoc
```php
/** @var UserDto[] */
public readonly array $users;
```

---

### Issue: Collection Empty After Creation

**Problem:**
```php
$collection = new DataCollection(UserDto::class, null);  // null items
```

**Solution:** Pass valid iterable
```php
$collection = new DataCollection(UserDto::class, []);    // Empty array
$collection = new DataCollection(UserDto::class, $data); // Valid data
```

---

### Issue: Cannot Modify Collection

**Problem:**
```php
$collection->items[] = $newItem;  // Error: readonly property
```

**Solution:** Collections are immutable by design. Create a new collection:
```php
$newItems = [...$collection->all(), $newItem];
$newCollection = new DataCollection(UserDto::class, $newItems);
```

---

### Issue: Pagination Metadata Missing

**Problem:**
```php
$paginated = PaginatedCollection::fromPaginator(UserDto::class, $object);
// $object doesn't have pagination methods
```

**Solution:** Use Laravel paginator or construct manually:
```php
$paginated = new PaginatedCollection(
    UserDto::class,
    $items,
    meta: [
        'current_page' => 1,
        'per_page' => 10,
        'total' => 100,
        'last_page' => 10,
    ]
);
```

---

## Summary

- ✅ **Simple arrays** with type casting for scalars and DTOs
- ✅ **DataCollection** for immutable DTO collections
- ✅ **PaginatedCollection** with metadata and links
- ✅ **Helper methods** for iteration, conversion, and access
- ✅ **Type-safe** with PHPDoc annotations
- ✅ **Immutable** design for thread-safety

---

**Next:** [Data Objects](./data-objects.md) | [Best Practices](./best-practices.md)
