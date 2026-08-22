<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Min;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\NumericRuleSupport;
use JOOservices\Dto\Validation\ValidatorInterface;

final class MinValidator implements ValidatorInterface
{
    public function __construct(
        private readonly NumericRuleSupport $numeric = new NumericRuleSupport(),
    ) {
    }

    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Min;
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
        if (! $rule instanceof Min || $value === null) {
            return;
        }

        $number = $this->numeric->asComparableFloat($value);
        if ($number === null) {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        if ($number >= (float) $rule->min) {
            return;
        }

        $this->fail($meta, $propertyName, $rule, $value);
    }

    /**
     * @throws ValidationException
     */
    private function fail(ClassMeta $meta, string $propertyName, Min $rule, mixed $value): never
    {
        $message = $rule->message ?? 'The value is below the minimum.';
        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $message,
            path: $propertyName,
            violations: [new RuleViolation($propertyName, 'min', $message, $reported)],
        );
    }
}
