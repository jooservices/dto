<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ValidatorInterface;

final class RequiredValidator implements ValidatorInterface
{
    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Required;
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
        if (! $rule instanceof Required) {
            return;
        }

        if ($value !== null && $value !== '') {
            return;
        }

        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $rule->message ?? 'This field is required.',
            path: $propertyName,
            violations: [
                new RuleViolation(
                    $propertyName,
                    'required',
                    $rule->message ?? 'This field is required.',
                    $reported,
                ),
            ],
        );
    }
}
