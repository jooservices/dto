<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

use stdClass;

final class OpenApiGenerator extends AbstractSchemaGenerator
{
    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, array<string, mixed>>  $defs
     * @return array<string, mixed>
     */
    protected function withDefinitions(array $schema, array $defs): array
    {
        return [
            'openapi' => '3.1.0',
            'info' => [
                'title' => 'DTO schemas',
                'version' => '3.2.0',
            ],
            'components' => [
                'schemas' => $defs,
            ],
            'paths' => new stdClass(),
            'x-root' => $schema,
        ];
    }

    protected function refPrefix(): string
    {
        return '#/components/schemas/';
    }
}
