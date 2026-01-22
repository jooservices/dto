<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\RequiredIf;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;

/**
 * Validates that a property is required if another field has a specific value.
 *
 * Supports properties with the #[RequiredIf] attribute.
 * Uses ValidationContext to access other field values.
 */
final class RequiredIfValidator implements ValidatorInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $property->hasAttribute(RequiredIf::class);
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        $attribute = $property->getAttribute(RequiredIf::class);

        if ($attribute === null) {
            return;
        }

        // Check if the condition field has the required value
        $conditionFieldValue = $context->getFieldValue($attribute->field);

        // Strict comparison for the condition
        if ($conditionFieldValue !== $attribute->value) {
            // Condition not met, field is not required
            return;
        }

        // Condition met, field is required - check if it has a value
        if ($this->isEmpty($value)) {
            throw ValidationException::fromViolations(
                "Validation failed for property '{$property->name}'",
                [
                    new RuleViolation(
                        propertyName: $property->name,
                        ruleName: 'required_if',
                        message: $attribute->getMessage(),
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
