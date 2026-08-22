<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Url;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\UrlSchemeGuard;
use JOOservices\Dto\Validation\ValidatorInterface;

final class UrlValidator implements ValidatorInterface
{
    public function __construct(
        private readonly UrlSchemeGuard $schemeGuard = new UrlSchemeGuard(),
    ) {
    }

    public function supports(ValidationRuleAttribute $rule): bool
    {
        return $rule instanceof Url;
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
        if (! $rule instanceof Url || $value === null) {
            return;
        }

        if ($value === '') {
            $this->fail($meta, $propertyName, $rule, $value);
        }

        if (is_string($value) && $this->schemeGuard->isAllowed($value, $rule->schemes)) {
            return;
        }

        $this->fail($meta, $propertyName, $rule, $value);
    }

    /**
     * @throws ValidationException
     */
    private function fail(ClassMeta $meta, string $propertyName, Url $rule, mixed $value): never
    {
        $message = $rule->message ?? 'Invalid URL.';
        $reported = $meta->property($propertyName)->redactWith ?? $value;
        throw new ValidationException(
            message: $message,
            path: $propertyName,
            violations: [new RuleViolation($propertyName, 'url', $message, $reported)],
        );
    }
}
