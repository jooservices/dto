# 📋 Schema Generation

Generate JSON Schema and OpenAPI specifications from your DTOs.

---

## Table of Contents
1. [Introduction](#introduction)
2. [JSON Schema Generation](#json-schema-generation)
3. [OpenAPI Generation](#openapi-generation)
4. [Schema Attributes](#schema-attributes)
5. [Real-World Use Cases](#real-world-use-cases)
6. [Integration Examples](#integration-examples)

---

## Introduction

Automatically generate schemas from your DTO classes for:

- **API Documentation** - Swagger UI, Redoc
- **Validation** - Frontend/backend validation
- **Contract Testing** - Verify API responses
- **Code Generation** - Generate clients from schemas

### Available Generators

| Generator | Output Format | Use Case |
|-----------|---------------|----------|
| `JsonSchemaGenerator` | JSON Schema draft-2020-12 | General validation, tools |
| `OpenApiGenerator` | OpenAPI 3.0 | API documentation, Swagger |

---

## JSON Schema Generation

Generate JSON Schema from DTOs for validation and tooling.

### Basic Example

```php
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Schema\JsonSchemaGenerator;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Meta\FileMetaCache;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?int $age = null,
    ) {}
}

// Create generator
$metaFactory = new MetaFactory(new FileMetaCache(__DIR__ . '/cache'));
$generator = new JsonSchemaGenerator($metaFactory);

// Generate schema
$schema = $generator->generate(UserDto::class);

echo json_encode($schema, JSON_PRETTY_PRINT);
```

**Output:**
```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "id": {
      "type": "integer"
    },
    "name": {
      "type": "string"
    },
    "email": {
      "type": "string"
    },
    "age": {
      "type": ["integer", "null"]
    }
  },
  "required": ["id", "name", "email"]
}
```

### With Validation Attributes

```php
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Min;
use JOOservices\Dto\Attributes\Validation\Max;

class RegisterDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $username,
        
        #[Required]
        #[Email]
        public readonly string $email,
        
        #[Min(18)]
        #[Max(120)]
        public readonly int $age,
    ) {}
}

$schema = $generator->generate(RegisterDto::class);
```

**Output:**
```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "username": {
      "type": "string"
    },
    "email": {
      "type": "string",
      "format": "email"
    },
    "age": {
      "type": "integer",
      "minimum": 18,
      "maximum": 120
    }
  },
  "required": ["username", "email", "age"]
}
```

### Nested DTOs

```php
class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
        public readonly string $zipCode,
    ) {}
}

class CustomerDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}
}

$schema = $generator->generate(CustomerDto::class);
```

**Output:**
```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "id": {"type": "integer"},
    "name": {"type": "string"},
    "address": {
      "type": "object",
      "properties": {
        "street": {"type": "string"},
        "city": {"type": "string"},
        "country": {"type": "string"},
        "zip_code": {"type": "string"}
      },
      "required": ["street", "city", "country", "zip_code"]
    }
  },
  "required": ["id", "name", "address"]
}
```

### Arrays of DTOs

```php
class TagDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $color,
    ) {}
}

class ArticleDto extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        /** @var TagDto[] */
        public readonly array $tags,
    ) {}
}

$schema = $generator->generate(ArticleDto::class);
```

**Output:**
```json
{
  "$schema": "https://json-schema.org/draft/2020-12/schema",
  "type": "object",
  "properties": {
    "title": {"type": "string"},
    "content": {"type": "string"},
    "tags": {
      "type": "array",
      "items": {
        "type": "object",
        "properties": {
          "name": {"type": "string"},
          "color": {"type": "string"}
        },
        "required": ["name", "color"]
      }
    }
  },
  "required": ["title", "content", "tags"]
}
```

---

## OpenAPI Generation

Generate OpenAPI 3.0 schemas for API documentation.

### Basic Example

```php
use JOOservices\Dto\Schema\OpenApiGenerator;

$generator = new OpenApiGenerator($metaFactory);
$schema = $generator->generate(UserDto::class);

echo json_encode($schema, JSON_PRETTY_PRINT);
```

**Output:**
```json
{
  "type": "object",
  "properties": {
    "id": {"type": "integer"},
    "name": {"type": "string"},
    "email": {"type": "string"}
  },
  "required": ["id", "name", "email"]
}
```

### Complete API Documentation

```php
class ApiDocumentation
{
    private OpenApiGenerator $generator;
    
    public function __construct(OpenApiGenerator $generator)
    {
        $this->generator = $generator;
    }
    
    public function generateSpec(): array
    {
        return [
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'My API',
                'version' => '1.0.0',
                'description' => 'API Documentation'
            ],
            'paths' => [
                '/users' => [
                    'get' => [
                        'summary' => 'List users',
                        'responses' => [
                            '200' => [
                                'description' => 'Success',
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            'type' => 'array',
                                            'items' => $this->generator->generate(UserDto::class)
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'post' => [
                        'summary' => 'Create user',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => $this->generator->generate(CreateUserDto::class)
                                ]
                            ]
                        ],
                        'responses' => [
                            '201' => [
                                'description' => 'Created',
                                'content' => [
                                    'application/json' => [
                                        'schema' => $this->generator->generate(UserDto::class)
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'components' => [
                'schemas' => [
                    'User' => $this->generator->generate(UserDto::class),
                    'CreateUser' => $this->generator->generate(CreateUserDto::class),
                ]
            ]
        ];
    }
}
```

### Multiple DTOs

```php
class ApiSchemas
{
    public function __construct(
        private readonly OpenApiGenerator $generator
    ) {}
    
    public function getComponents(): array
    {
        return [
            'schemas' => [
                'User' => $this->generator->generate(UserDto::class),
                'Product' => $this->generator->generate(ProductDto::class),
                'Order' => $this->generator->generate(OrderDto::class),
                'Address' => $this->generator->generate(AddressDto::class),
            ]
        ];
    }
}
```

---

## Schema Attributes

Attributes that affect schema generation:

### @Required

Makes property required in schema.

```php
class UserDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $email,
        
        public readonly ?string $phone = null,  // Optional
    ) {}
}
```

**Schema:**
```json
{
  "properties": {
    "email": {"type": "string"},
    "phone": {"type": ["string", "null"]}
  },
  "required": ["email"]
}
```

### @Hidden

Excludes property from schema.

```php
use JOOservices\Dto\Attributes\Hidden;

class UserDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        
        #[Hidden]
        public readonly string $passwordHash,
    ) {}
}
```

**Schema:**
```json
{
  "properties": {
    "id": {"type": "integer"},
    "name": {"type": "string"}
  },
  "required": ["id", "name"]
}
```

### @Email

Adds email format validation.

```php
class ContactDto extends Dto
{
    public function __construct(
        #[Email]
        public readonly string $email,
    ) {}
}
```

**Schema:**
```json
{
  "properties": {
    "email": {
      "type": "string",
      "format": "email"
    }
  }
}
```

### @Url

Adds URL format validation.

```php
use JOOservices\Dto\Attributes\Validation\Url;

class ProfileDto extends Dto
{
    public function __construct(
        #[Url]
        public readonly string $website,
    ) {}
}
```

**Schema:**
```json
{
  "properties": {
    "website": {
      "type": "string",
      "format": "uri"
    }
  }
}
```

---

## Real-World Use Cases

### Use Case 1: API Documentation

```php
// Generate OpenAPI spec for Swagger UI
class SwaggerController
{
    public function getSpec(): Response
    {
        $generator = new OpenApiGenerator($metaFactory);
        
        $spec = [
            'openapi' => '3.0.0',
            'info' => ['title' => 'My API', 'version' => '1.0.0'],
            'components' => [
                'schemas' => [
                    'User' => $generator->generate(UserDto::class),
                    'Product' => $generator->generate(ProductDto::class),
                    'Order' => $generator->generate(OrderDto::class),
                ]
            ]
        ];
        
        return response()->json($spec);
    }
}
```

### Use Case 2: Frontend Validation

```php
// Generate JSON Schema for frontend validation
class ValidationSchemaController
{
    public function getUserSchema(): Response
    {
        $generator = new JsonSchemaGenerator($metaFactory);
        $schema = $generator->generate(CreateUserDto::class);
        
        return response()->json($schema);
    }
}

// Frontend (JavaScript) can use this schema
// with libraries like Ajv for validation
```

### Use Case 3: Contract Testing

```php
use PHPUnit\Framework\TestCase;

class ApiContractTest extends TestCase
{
    public function testUserResponseMatchesSchema()
    {
        $generator = new JsonSchemaGenerator($metaFactory);
        $schema = $generator->generate(UserDto::class);
        
        // Call API
        $response = $this->client->get('/api/users/1');
        $data = json_decode($response->getBody(), true);
        
        // Validate against schema
        $validator = new JsonValidator();
        $result = $validator->validate($data, $schema);
        
        $this->assertTrue($result->isValid());
    }
}
```

### Use Case 4: TypeScript Generation

```php
class TypeScriptGenerator
{
    public function generateInterfaces(): string
    {
        $generator = new JsonSchemaGenerator($metaFactory);
        
        $types = [
            'User' => $generator->generate(UserDto::class),
            'Product' => $generator->generate(ProductDto::class),
        ];
        
        $typescript = '';
        foreach ($types as $name => $schema) {
            $typescript .= $this->schemaToTypeScript($name, $schema);
        }
        
        return $typescript;
    }
}
```

---

## Integration Examples

### Swagger UI Integration

```php
// In your framework's routes
Route::get('/api/documentation', function () {
    $generator = new OpenApiGenerator($metaFactory);
    
    $spec = [
        'openapi' => '3.0.0',
        'info' => [
            'title' => 'My API',
            'version' => '1.0.0'
        ],
        'components' => [
            'schemas' => [
                'User' => $generator->generate(UserDto::class),
                // ... more schemas
            ]
        ]
    ];
    
    return view('swagger-ui', ['spec' => json_encode($spec)]);
});
```

```html
<!-- swagger-ui.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@latest/swagger-ui.css"/>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@latest/swagger-ui-bundle.js"></script>
    <script>
        SwaggerUIBundle({
            spec: {!! $spec !!},
            dom_id: '#swagger-ui',
        });
    </script>
</body>
</html>
```

### Redoc Integration

```php
Route::get('/api/docs', function () {
    $spec = /* generate OpenAPI spec */;
    return view('redoc', ['spec' => json_encode($spec)]);
});
```

```html
<!-- redoc.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>API Documentation</title>
</head>
<body>
    <redoc spec-url="/api/openapi.json"></redoc>
    <script src="https://cdn.jsdelivr.net/npm/redoc@latest/bundles/redoc.standalone.js"></script>
</body>
</html>
```

---

## Best Practices

### 1. Cache Generated Schemas

```php
class SchemaCache
{
    private array $cache = [];
    
    public function getSchema(string $dtoClass): array
    {
        if (!isset($this->cache[$dtoClass])) {
            $generator = new JsonSchemaGenerator($metaFactory);
            $this->cache[$dtoClass] = $generator->generate($dtoClass);
        }
        
        return $this->cache[$dtoClass];
    }
}
```

### 2. Version Your Schemas

```php
class ApiVersioning
{
    public function getSchemaForVersion(string $version, string $dtoClass): array
    {
        $generator = new OpenApiGenerator($metaFactory);
        $schema = $generator->generate($dtoClass);
        
        // Add version-specific modifications
        $schema['x-api-version'] = $version;
        
        return $schema;
    }
}
```

### 3. Document with Descriptions

```php
// Add descriptions via PHPDoc
/**
 * User data transfer object.
 * 
 * Represents a user in the system with all required fields.
 */
class UserDto extends Dto
{
    public function __construct(
        /** Unique user identifier */
        public readonly int $id,
        
        /** User's full name */
        public readonly string $name,
    ) {}
}
```

---

## Summary

- ✅ **JsonSchemaGenerator** - JSON Schema draft-2020-12 for validation
- ✅ **OpenApiGenerator** - OpenAPI 3.0 for API documentation
- ✅ Respects validation attributes (@Required, @Email, @Url, @Hidden)
- ✅ Supports nested DTOs and arrays
- ✅ Integrate with Swagger UI, Redoc
- ✅ Use for validation, contract testing, code generation
- ✅ Cache schemas for performance

---

**Next:** [Advanced Attributes](./advanced-attributes.md) | [Optional & Partial](./optional-partial.md)
