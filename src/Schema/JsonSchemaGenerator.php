<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Generate JSON Schema (draft-2020-12) from DTO classes.
 */
final readonly class JsonSchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private MetaFactoryInterface $metaFactory,
    ) {}

    public function getFormat(): string
    {
        return 'json-schema';
    }

    /**
     * @param  class-string  $dtoClass
     * @return array<string, mixed>
     */
    public function generate(string $dtoClass): array
    {
        $meta = $this->metaFactory->create($dtoClass);

        $schema = [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            'type' => 'object',
            'properties' => [],
            'required' => [],
        ];

        foreach ($meta->properties as $property) {
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
            'int' => ['type' => 'integer'],
            'float' => ['type' => 'number'],
            'bool', 'boolean' => ['type' => 'boolean'],
            'array' => ['type' => 'array'],
            default => ['type' => 'object'],
        };

        if ($property->type->isNullable) {
            $schema = [
                'anyOf' => [
                    $schema,
                    ['type' => 'null'],
                ],
            ];
        }

        return $schema;
    }
}
