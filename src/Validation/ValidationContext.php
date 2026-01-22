<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Context passed to validators during validation.
 *
 * Provides access to:
 * - The current property being validated
 * - All input data (for conditional validators like RequiredIf)
 * - The main DTO context
 */
final readonly class ValidationContext
{
    /**
     * @param  array<string, mixed>  $allData  All input data being validated
     */
    public function __construct(
        public PropertyMeta $property,
        public array $allData,
        public Context $context,
    ) {}

    /**
     * Check if a field exists in the input data.
     */
    public function hasField(string $field): bool
    {
        return array_key_exists($field, $this->allData);
    }

    /**
     * Get the value of a field from input data.
     *
     * Returns null if the field doesn't exist.
     */
    public function getFieldValue(string $field): mixed
    {
        return $this->allData[$field] ?? null;
    }
}
