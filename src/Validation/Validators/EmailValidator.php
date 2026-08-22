<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ValidatorInterface;

final class EmailValidator implements ValidatorInterface
{
    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Email;
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
        unset($values, $ctx);
        if (! $rule instanceof Email || $value === null) {
            return;
        }

        if ($value === '') {
            $message = $rule->message ?? 'Invalid email.';
            $reported = $meta->property($propertyName)->redactWith ?? $value;
            throw new ValidationException(
                message: $message,
                path: $propertyName,
                violations: [new RuleViolation($propertyName, 'email', $message, $reported)],
            );
        }

        if (is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL) !== false) {
            return;
        }

        $message = $rule->message ?? 'Invalid email.';
        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $message,
            path: $propertyName,
            violations: [new RuleViolation($propertyName, 'email', $message, $reported)],
        );
    }
}
