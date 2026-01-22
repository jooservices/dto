<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

/**
 * Interface for generating schemas from DTO classes.
 */
interface SchemaGeneratorInterface
{
    /**
     * Generate schema for a DTO class.
     *
     * @param  class-string  $dtoClass
     * @return array<string, mixed>
     */
    public function generate(string $dtoClass): array;

    /**
     * Get the format identifier for this generator.
     *
     * @return string 'json-schema' | 'openapi-3.0' | 'openapi-3.1'
     */
    public function getFormat(): string;
}
