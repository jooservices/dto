<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

final class JsonSchemaGenerator extends AbstractSchemaGenerator
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, array<string, mixed>>  $defs
     * @return array<string, mixed>
     */
    protected function withDefinitions(array $schema, array $defs): array
    {
        return [
            '$schema' => 'https://json-schema.org/draft/2020-12/schema',
            '$ref' => $schema['$ref'] ?? null,
            '$defs' => $defs,
        ];
    }

    protected function refPrefix(): string
    {
        return '#/$defs/';
    }
}
