<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Min;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a numeric value is at least a minimum value.
 *
 * Supports properties with the #[Min] attribute.
 * Null values are allowed - use #[Required] to enforce non-null.
 */
final class MinValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Min::class);
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

        $attribute = $property->getAttribute(Min::class);

        if ($attribute === null) {
            return;
        }

        if ((float) $value < $attribute->min) {
            $this->throwValidation($property, $value);
        }
    }

    private function throwValidation(PropertyMeta $property, mixed $value): never
    {
        $attribute = $property->getAttribute(Min::class);

        throw ValidationException::fromViolations(
            "Validation failed for property '{$property->name}'",
            [
                new RuleViolation(
                    propertyName: $property->name,
                    ruleName: 'min',
                    message: $attribute?->getMessage() ?? 'The value is too small',
                    invalidValue: $value,
                ),
            ],
            $property->name,
        );
    }
}
