<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Length;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a string's length is within specified bounds.
 *
 * Supports properties with the #[Length] attribute.
 * Null values are allowed - use #[Required] to enforce non-null.
 */
final class LengthValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Length::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        // Null values are handled by RequiredValidator
        if ($value === null) {
            return;
        }

        // Must be a string to check length
        if (! is_string($value)) {
            $this->throwValidation($property, $value);
        }

        $attribute = $property->getAttribute(Length::class);

        if ($attribute === null) {
            return;
        }

        $length = mb_strlen($value);

        if ($attribute->min !== null && $length < $attribute->min) {
            $this->throwValidation($property, $value);
        }

        if ($attribute->max !== null && $length > $attribute->max) {
            $this->throwValidation($property, $value);
        }
    }

    private function throwValidation(PropertyMeta $property, mixed $value): never
    {
        $attribute = $property->getAttribute(Length::class);

        throw ValidationException::fromViolations(
            "Validation failed for property '{$property->name}'",
            [
                new RuleViolation(
                    propertyName: $property->name,
                    ruleName: 'length',
                    message: $attribute?->getMessage() ?? 'Invalid length',
                    invalidValue: $value,
                ),
            ],
            $property->name,
        );
    }
}
