<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Interface for the validator registry.
 *
 * Follows the same pattern as CasterRegistryInterface.
 */
interface ValidatorRegistryInterface
{
    /**
     * Register a validator with optional priority.
     *
     * Higher priority validators are checked first.
     */
    public function register(ValidatorInterface $validator, int $priority = 0): void;

    /**
     * Get the first validator that supports the property.
     */
    public function get(PropertyMeta $property, mixed $value): ?ValidatorInterface;

    /**
     * Run all applicable validators for a property.
     *
     * Collects all violations and throws a single ValidationException.
     *
     * @throws \JOOservices\Dto\Exceptions\ValidationException on validation failure
     */
    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void;

    /**
     * Check if any validator can handle the property.
     */
    public function canValidate(PropertyMeta $property, mixed $value): bool;
}
