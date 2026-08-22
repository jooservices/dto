<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ValidatorInterface;

final class RegexValidator implements ValidatorInterface
{
    private const int MAX_BYTES = 4096;

    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Regex;
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
        if (! $rule instanceof Regex || $value === null) {
            return;
        }

        if (! is_string($value)) {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        if (strlen($value) > self::MAX_BYTES) {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        $matched = preg_match($rule->pattern, $value);
        if ($matched === false || preg_last_error() !== PREG_NO_ERROR) {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        if ($matched !== 1) {
            $this->fail($meta, $propertyName, $rule, $value);
        }
    }

    /**
     * @throws ValidationException
     */
    private function fail(ClassMeta $meta, string $propertyName, Regex $rule, mixed $value): never
    {
        $message = $rule->message ?? 'The value does not match the required pattern.';
        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $message,
            path: $propertyName,
            violations: [new RuleViolation($propertyName, 'regex', $message, $reported)],
        );
    }
}
