<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a property has a non-null, non-empty value.
 *
 * Supports properties with the #[Required] attribute.
 */
final class RequiredValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(Required::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        if ($this->isEmpty($value)) {
            $attribute = $property->getAttribute(Required::class);

            throw ValidationException::fromViolations(
                "Validation failed for property '{$property->name}'",
                [
                    new RuleViolation(
                        propertyName: $property->name,
                        ruleName: 'required',
                        message: $attribute?->getMessage() ?? 'This field is required',
                        invalidValue: $value,
                    ),
                ],
                $property->name,
            );
        }
    }

    private function isEmpty(mixed $value): bool
    {
        if ($value === null) {
            return true;
        }

        if ($value === '') {
            return true;
        }

        return is_array($value) && $value === [];
    }
}
