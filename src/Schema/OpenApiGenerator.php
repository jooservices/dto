<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Generate OpenAPI 3.0 schemas from DTO classes.
 *
 * Commonly used for API documentation (Swagger UI, Redoc).
 */
final readonly class OpenApiGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private MetaFactoryInterface $metaFactory,
    ) {}

    public function getFormat(): string
    {
        return 'openapi-3.0';
    }

    /**
     * @param  class-string  $dtoClass
     * @return array<string, mixed>
     */
    public function generate(string $dtoClass): array
    {
        $meta = $this->metaFactory->create($dtoClass);

        $schema = [
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        foreach ($meta->properties as $property) {
            // Skip hidden properties in API schema
            if ($property->isHidden) {
                continue;
            }

            $schema['properties'][$property->name] = $this->propertyToSchema($property);

            if ($property->isRequired()) {
                $schema['required'][] = $property->name;
            }
        }

        if ($schema['required'] === []) {
            unset($schema['required']);
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>
     */
    private function propertyToSchema(PropertyMeta $property): array
    {
        $schema = match ($property->type->name) {
            'string' => ['type' => 'string'],
            'int' => ['type' => 'integer', 'format' => 'int64'],
            'float' => ['type' => 'number', 'format' => 'double'],
            'bool', 'boolean' => ['type' => 'boolean'],
            'array' => ['type' => 'array', 'items' => ['type' => 'object']],
            default => ['type' => 'object'],
        };

        // Add validation constraints as schema constraints
        foreach ($property->validationRules as $rule) {
            if ($rule instanceof Email) {
                $schema['format'] = 'email';
            }
        }

        if ($property->type->isNullable) {
            $schema['nullable'] = true;
        }

        return $schema;
    }
}
