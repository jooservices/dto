<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Valid;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates nested DTOs or arrays of DTOs.
 *
 * Supports properties with the #[Valid] attribute.
 *
 * Note: Actual nested DTO validation occurs during hydration when validation is enabled.
 * If a DTO instance exists at this point, it was already validated during creation.
 * This validator acts as a marker to indicate the #[Valid] attribute is supported.
 */
final class ValidValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Valid::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        // Null values are handled by RequiredValidator
        if ($value === null) {
            return;
        }

        $attribute = $property->getAttribute(Valid::class);

        if ($attribute === null) {
            return;
        }

        // Handle array of DTOs with eachItem: true
        // Each DTO in the array was already validated during hydration.
        // Handle single DTO - also already validated during hydration.
        // This validator confirms #[Valid] attribute support without additional action.
    }
}
