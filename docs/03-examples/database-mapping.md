# 🗄️ Database Mapping Examples

Real-world examples of mapping database records to DTOs in **jooservices/dto**.

---

## Table of Contents
1. [Basic Model to DTO](#basic-model-to-DTO)
2. [Eloquent Collections](#eloquent-collections)
3. [Relationships](#relationships)
4. [Query Results](#query-results)
5. [Pagination](#pagination)
6. [Repository Pattern](#repository-pattern)
7. [Doctrine Integration](#doctrine-integration)

---

## Basic Model to DTO

### Simple Mapping

```php
// Eloquent Model
class User extends Model
{
    protected $fillable = ['name', 'email', 'created_at'];
}

// DTO
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly DateTimeImmutable $createdAt,
    ) {}
}

// Usage:
$user = User::find(1);

$userDto = UserDto::from([
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'created_at' => $user->created_at->toImmutable(),
]);

// Or using toArray():
$userDto = UserDto::from($user->toArray());
```

### With Mapper Class

```php
class UserMapper
{
    public static function toDto(User $user): UserDto
    {
        return UserDto::from([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toImmutable(),
        ]);
    }
    
    public static function fromDto(UserDto $dto): User
    {
        return new User([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);
    }
    
    public static function updateFromDto(User $user, UserDto $dto): User
    {
        $user->fill([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);
        
        return $user;
    }
}

// Usage:
$user = User::find(1);
$dto = UserMapper::toDto($user);

// Create new:
$newUser = UserMapper::fromDto($dto);
$newUser->save();

// Update existing:
$user = UserMapper::updateFromDto($user, $dto);
$user->save();
```

---

## Eloquent Collections

### Mapping Collections

```php
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}

// Controller:
class UserController
{
    public function index()
    {
        $users = User::all();
        
        // Map collection to DTOs:
        $userDtos = $users->map(fn($user) => UserDto::from($user->toArray()));
        
        return response()->json($userDtos);
    }
}
```

### Using DataCollection

```php
use JOOservices\Dto\Collections\DataCollection;

class UserController
{
    public function index()
    {
        $users = User::all();
        
        // Convert to DTO collection:
        $collection = new DataCollection(
            UserDto::class,
            $users->toArray()
        );
        
        return response()->json($collection->toArray());
    }
}
```

### With Repository

```php
interface UserRepositoryInterface
{
    public function all(): DataCollection;
    public function find(int $id): ?UserDto;
}

class UserRepository implements UserRepositoryInterface
{
    public function all(): DataCollection
    {
        $users = User::all();
        
        return new DataCollection(
            UserDto::class,
            $users->toArray()
        );
    }
    
    public function find(int $id): ?UserDto
    {
        $user = User::find($id);
        
        if ($user === null) {
            return null;
        }
        
        return UserDto::from($user->toArray());
    }
}
```

---

## Relationships

### One-to-One

```php
// Models
class User extends Model
{
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }
}

class Profile extends Model
{
    protected $fillable = ['bio', 'avatar_url'];
}

// DTOs
class ProfileDto extends Dto
{
    public function __construct(
        public readonly string $bio,
        public readonly ?string $avatarUrl,
    ) {}
}

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?ProfileDto $profile,
    ) {}
}

// Mapping:
$user = User::with('profile')->find(1);

$userDto = UserDto::from([
    'id' => $user->id,
    'name' => $user->name,
    'email' => $user->email,
    'profile' => $user->profile ? [
        'bio' => $user->profile->bio,
        'avatar_url' => $user->profile->avatar_url,
    ] : null,
]);
```

### One-to-Many

```php
// Models
class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    protected $fillable = ['text', 'author'];
}

// DTOs
class CommentDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $text,
        public readonly string $author,
    ) {}
}

class PostDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly string $content,
        /** @var CommentDto[] */
        public readonly array $comments,
    ) {}
}

// Mapping:
$post = Post::with('comments')->find(1);

$postDto = PostDto::from([
    'id' => $post->id,
    'title' => $post->title,
    'content' => $post->content,
    'comments' => $post->comments->map(fn($comment) => [
        'id' => $comment->id,
        'text' => $comment->text,
        'author' => $comment->author,
    ])->toArray(),
]);
```

### Many-to-Many

```php
// Models
class User extends Model
{
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }
}

class Role extends Model
{
    protected $fillable = ['name', 'description'];
}

// DTOs
class RoleDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $description,
    ) {}
}

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        /** @var RoleDto[] */
        public readonly array $roles,
    ) {}
}

// Mapping:
$user = User::with('roles')->find(1);

$userDto = UserDto::from([
    'id' => $user->id,
    'name' => $user->name,
    'roles' => $user->roles->map(fn($role) => [
        'id' => $role->id,
        'name' => $role->name,
        'description' => $role->description,
    ])->toArray(),
]);
```

---

## Query Results

### Raw Queries

```php
use Illuminate\Support\Facades\DB;

class OrderDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $customerName,
        public readonly float $total,
        public readonly int $itemCount,
    ) {}
}

// Raw query:
$results = DB::select('
    SELECT 
        o.id,
        u.name as customer_name,
        SUM(oi.price * oi.quantity) as total,
        COUNT(oi.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id, u.name
');

// Map to DTOs:
$orders = array_map(
    fn($row) => OrderDto::from([
        'id' => $row->id,
        'customer_name' => $row->customer_name,
        'total' => $row->total,
        'item_count' => $row->item_count,
    ]),
    $results
);
```

### Query Builder

```php
class ReportDto extends Dto
{
    public function __construct(
        public readonly string $month,
        public readonly int $userCount,
        public readonly float $revenue,
    ) {}
}

// Query builder:
$reports = DB::table('orders')
    ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month')
    ->selectRaw('COUNT(DISTINCT user_id) as user_count')
    ->selectRaw('SUM(total) as revenue')
    ->groupBy('month')
    ->get();

// Map to DTOs:
$reportDtos = $reports->map(fn($row) => ReportDto::from([
    'month' => $row->month,
    'user_count' => $row->user_count,
    'revenue' => $row->revenue,
]))->toArray();
```

---

## Pagination

### Simple Pagination

```php
use JOOservices\Dto\Collections\PaginatedCollection;

class UserController
{
    public function index()
    {
        // Get paginated Eloquent results:
        $users = User::paginate(10);
        
        // Convert to paginated DTO collection:
        $collection = PaginatedCollection::fromPaginator(
            UserDto::class,
            $users
        );
        
        return response()->json($collection->toArray());
        // {
        //     "data": [...],
        //     "meta": {
        //         "current_page": 1,
        //         "per_page": 10,
        //         "total": 50,
        //         "last_page": 5
        //     },
        //     "links": {
        //         "first": "...",
        //         "last": "...",
        //         "prev": null,
        //         "next": "..."
        //     }
        // }
    }
}
```

### Custom Pagination

```php
class ProductController
{
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        
        // Get paginated products:
        $paginator = Product::query()
            ->where('active', true)
            ->orderBy('name')
            ->paginate($perPage);
        
        // Convert to DTOs:
        $collection = PaginatedCollection::fromPaginator(
            ProductDto::class,
            $paginator
        )->wrap('products');  // Wrap data key
        
        return response()->json($collection->toArray());
    }
}
```

---

## Repository Pattern

### Repository Interface

```php
interface UserRepositoryInterface
{
    public function find(int $id): ?UserDto;
    public function all(): DataCollection;
    public function paginate(int $perPage = 10): PaginatedCollection;
    public function create(array $data): UserDto;
    public function update(int $id, array $data): UserDto;
    public function delete(int $id): bool;
}
```

### Repository Implementation

```php
use JOOservices\Dto\Collections\DataCollection;
use JOOservices\Dto\Collections\PaginatedCollection;

class UserRepository implements UserRepositoryInterface
{
    public function find(int $id): ?UserDto
    {
        $user = User::find($id);
        
        if ($user === null) {
            return null;
        }
        
        return $this->toDto($user);
    }
    
    public function all(): DataCollection
    {
        $users = User::all();
        
        return new DataCollection(
            UserDto::class,
            $users->map(fn($user) => $this->toArray($user))->toArray()
        );
    }
    
    public function paginate(int $perPage = 10): PaginatedCollection
    {
        $paginator = User::paginate($perPage);
        
        return PaginatedCollection::fromPaginator(
            UserDto::class,
            $paginator
        );
    }
    
    public function create(array $data): UserDto
    {
        $user = User::create($data);
        
        return $this->toDto($user);
    }
    
    public function update(int $id, array $data): UserDto
    {
        $user = User::findOrFail($id);
        $user->update($data);
        
        return $this->toDto($user->fresh());
    }
    
    public function delete(int $id): bool
    {
        $user = User::findOrFail($id);
        
        return $user->delete();
    }
    
    private function toDto(User $user): UserDto
    {
        return UserDto::from($this->toArray($user));
    }
    
    private function toArray(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at->toImmutable(),
        ];
    }
}
```

### Service Layer

```php
class UserService
{
    public function __construct(
        private UserRepositoryInterface $repository
    ) {}
    
    public function getUserById(int $id): UserDto
    {
        $user = $this->repository->find($id);
        
        if ($user === null) {
            throw new ModelNotFoundException("User not found");
        }
        
        return $user;
    }
    
    public function getAllUsers(): DataCollection
    {
        return $this->repository->all();
    }
    
    public function createUser(UserData $data): UserDto
    {
        return $this->repository->create($data->toArray());
    }
    
    public function updateUser(int $id, UserData $data): UserDto
    {
        return $this->repository->update($id, $data->toArray());
    }
}
```

---

## Doctrine Integration

### Entity to DTO

```php
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class UserEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private int $id;
    
    #[ORM\Column(type: 'string', length: 255)]
    private string $name;
    
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;
    
    // Getters...
}

// DTO
class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
    ) {}
}

// Mapper
class DoctrineUserMapper
{
    public static function toDto(UserEntity $entity): UserDto
    {
        return UserDto::from([
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'email' => $entity->getEmail(),
        ]);
    }
    
    public static function toEntity(UserDto $dto): UserEntity
    {
        $entity = new UserEntity();
        $entity->setName($dto->name);
        $entity->setEmail($dto->email);
        
        return $entity;
    }
}
```

### Doctrine Repository

```php
use Doctrine\ORM\EntityRepository;
use JOOservices\Dto\Collections\DataCollection;

class DoctrineUserRepository extends EntityRepository
{
    public function findDto(int $id): ?UserDto
    {
        $entity = $this->find($id);
        
        if ($entity === null) {
            return null;
        }
        
        return DoctrineUserMapper::toDto($entity);
    }
    
    public function findAllDtos(): DataCollection
    {
        $entities = $this->findAll();
        
        $data = array_map(
            fn($entity) => [
                'id' => $entity->getId(),
                'name' => $entity->getName(),
                'email' => $entity->getEmail(),
            ],
            $entities
        );
        
        return new DataCollection(UserDto::class, $data);
    }
}
```

---

## Summary

- ✅ Map **Eloquent models** to DTOs with mappers
- ✅ Use **DataCollection** for model collections
- ✅ Handle **relationships** (one-to-one, one-to-many, many-to-many)
- ✅ Map **raw queries** and query builder results
- ✅ Use **PaginatedCollection** for paginated data
- ✅ Implement **repository pattern** with DTOs
- ✅ Integrate with **Doctrine ORM**

---

**Next:** [Real-World Scenarios](./real-world-scenarios.md) | [Basic Examples](./basic-examples.md)
