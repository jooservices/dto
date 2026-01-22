<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Interface for validation rules.
 *
 * Follows the same pattern as CasterInterface:
 * - supports() determines if this validator handles the property
 * - validate() performs the validation
 */
interface ValidatorInterface
{
    /**
     * Check if this validator can handle the given property.
     *
     * Typically checks if the property has a specific validation attribute.
     */
    public function supports(PropertyMeta $property, mixed $value): bool;

    /**
     * Validate the value.
     *
     * @throws \JOOservices\Dto\Exceptions\ValidationException on validation failure
     */
    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void;
}
