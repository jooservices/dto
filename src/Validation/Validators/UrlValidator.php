<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Url;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a value is a valid URL.
 *
 * Supports properties with the #[Url] attribute.
 * Null values are allowed - use #[Required] to enforce non-null.
 */
final class UrlValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Url::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        // Null values are handled by RequiredValidator
        if ($value === null) {
            return;
        }

        // Must be a string to validate as URL
        if (! is_string($value)) {
            $this->throwValidation($property, $value);
        }

        // Empty string is valid (use Required for non-empty)
        if ($value === '') {
            return;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            $this->throwValidation($property, $value);
        }
    }

    private function throwValidation(PropertyMeta $property, mixed $value): never
    {
        $attribute = $property->getAttribute(Url::class);

        throw ValidationException::fromViolations(
            "Validation failed for property '{$property->name}'",
            [
                new RuleViolation(
                    propertyName: $property->name,
                    ruleName: 'url',
                    message: $attribute?->getMessage() ?? 'The value must be a valid URL',
                    invalidValue: $value,
                ),
            ],
            $property->name,
        );
    }
}
