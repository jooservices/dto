<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Max;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a numeric value is at most a maximum value.
 *
 * Supports properties with the #[Max] attribute.
 * Null values are allowed - use #[Required] to enforce non-null.
 */
final class MaxValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Max::class);
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

        $attribute = $property->getAttribute(Max::class);

        if ($attribute === null) {
            return;
        }

        if ((float) $value > $attribute->max) {
            $this->throwValidation($property, $value);
        }
    }

    private function throwValidation(PropertyMeta $property, mixed $value): never
    {
        $attribute = $property->getAttribute(Max::class);

        throw ValidationException::fromViolations(
            "Validation failed for property '{$property->name}'",
            [
                new RuleViolation(
                    propertyName: $property->name,
                    ruleName: 'max',
                    message: $attribute?->getMessage() ?? 'The value is too large',
                    invalidValue: $value,
                ),
            ],
            $property->name,
        );
    }
}
