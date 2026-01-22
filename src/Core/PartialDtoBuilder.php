<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

/**
 * Builder for creating partial DTOs with only specified fields hydrated.
 *
 * Useful for partial updates where only certain fields should be populated.
 */
final readonly class PartialDtoBuilder
{
    /**
     * @param  class-string  $dtoClass
     * @param  array<string>  $allowedFields
     */
    public function __construct(
        private string $dtoClass,
        private array $allowedFields,
    ) {}

    /**
     * Create a partial DTO from the given source.
     *
     * Only fields specified in allowedFields will be hydrated.
     */
    public function from(mixed $source, ?Context $ctx = null): object
    {
        // Convert source to array
        $data = is_array($source) ? $source : (array) $source;

        // Filter to only allowed fields
        $filtered = array_intersect_key($data, array_flip($this->allowedFields));

        // Hydrate using the standard from() method
        return $this->dtoClass::from($filtered, $ctx);
    }

    /**
     * Get the list of allowed fields.
     *
     * @return array<string>
     */
    public function getAllowedFields(): array
    {
        return $this->allowedFields;
    }

    /**
     * Get the target DTO class.
     *
     * @return class-string
     */
    public function getDtoClass(): string
    {
        return $this->dtoClass;
    }
}
