<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Between;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a numeric value is between a minimum and maximum value (inclusive).
 *
 * Supports properties with the #[Between] attribute.
 * Null values are allowed - use #[Required] to enforce non-null.
 */
final class BetweenValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Between::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        // Null values are handled by RequiredValidator
        if ($value === null) {
            return;
        }

        // Must be numeric
        if (! is_numeric($value)) {
            $this->throwValidation($property, $value);
        }

        $attribute = $property->getAttribute(Between::class);

        if ($attribute === null) {
            return;
        }

        $numericValue = (float) $value;

        if ($numericValue < $attribute->min || $numericValue > $attribute->max) {
            $this->throwValidation($property, $value);
        }
    }

    private function throwValidation(PropertyMeta $property, mixed $value): never
    {
        $attribute = $property->getAttribute(Between::class);

        throw ValidationException::fromViolations(
            "Validation failed for property '{$property->name}'",
            [
                new RuleViolation(
                    propertyName: $property->name,
                    ruleName: 'between',
                    message: $attribute?->getMessage() ?? 'The value is out of range',
                    invalidValue: $value,
                ),
            ],
            $property->name,
        );
    }
}
