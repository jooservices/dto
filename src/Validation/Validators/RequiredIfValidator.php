<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\RequiredIf;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ValidatorInterface;

final class RequiredIfValidator implements ValidatorInterface
{
    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof RequiredIf;
    }

    /**
     * @throws ValidationException
     */
    public function validate(
        ValidationRuleAttribute $rule,
        ClassMeta $meta,
        string $propertyName,
        mixed $value,
        array $values,
        ?Context $ctx,
    ): void {
        unset($ctx);
        if (! $rule instanceof RequiredIf) {
            return;
        }

        $other = $values[$rule->field] ?? null;
        if ($other !== $rule->value) {
            return;
        }

        if ($value !== null && $value !== '') {
            return;
        }

        $message = $rule->message ?? 'This field is required when ' . $rule->field . ' matches.';
        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $message,
            path: $propertyName,
            violations: [new RuleViolation($propertyName, 'required_if', $message, $reported)],
        );
    }
}
