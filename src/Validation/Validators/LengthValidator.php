<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Length;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ValidatorInterface;

final class LengthValidator implements ValidatorInterface
{
    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Length;
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
        if (! $rule instanceof Length || $value === null) {
            return;
        }

        $length = $this->measure($value);
        if ($length < 0) {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        if ($rule->min !== null && $length < $rule->min) {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        if ($rule->max !== null && $length > $rule->max) {
            $this->fail($meta, $propertyName, $rule, $value);
        }
    }

    private function measure(mixed $value): int
    {
        if (is_string($value)) {
            return mb_strlen($value, 'UTF-8');
        }

        if (is_countable($value)) {
            return count($value);
        }

        return -1;
    }

    /**
     * @throws ValidationException
     */
    private function fail(ClassMeta $meta, string $propertyName, Length $rule, mixed $value): never
    {
        $message = $rule->message ?? 'Invalid length.';
        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $message,
            path: $propertyName,
            violations: [new RuleViolation($propertyName, 'length', $message, $reported)],
        );
    }
}
