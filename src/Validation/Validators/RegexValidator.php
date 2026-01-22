<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a value matches a regular expression pattern.
 *
 * Supports properties with the #[Regex] attribute.
 * Null values are allowed - use #[Required] to enforce non-null.
 */
final class RegexValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Regex::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        // Null values are handled by RequiredValidator
        if ($value === null) {
            return;
        }

        // Must be a string to match regex
        if (! is_string($value)) {
            $this->throwValidation($property, $value);
        }

        // Empty string is valid (use Required for non-empty)
        if ($value === '') {
            return;
        }

        $attribute = $property->getAttribute(Regex::class);

        if ($attribute === null) {
            return;
        }

        if (preg_match($attribute->pattern, $value) !== 1) {
            $this->throwValidation($property, $value);
        }
    }

    private function throwValidation(PropertyMeta $property, mixed $value): never
    {
        $attribute = $property->getAttribute(Regex::class);

        throw ValidationException::fromViolations(
            "Validation failed for property '{$property->name}'",
            [
                new RuleViolation(
                    propertyName: $property->name,
                    ruleName: 'regex',
                    message: $attribute?->getMessage() ?? 'The value does not match the required pattern',
                    invalidValue: $value,
                ),
            ],
            $property->name,
        );
    }
}
