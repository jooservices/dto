<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

interface ValidatorInterface
{
    public function supports(ValidationRuleAttribute $rule): bool;

    /**
     * @param  array<string, mixed>  $values
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validate(
        ValidationRuleAttribute $rule,
        ClassMeta $meta,
        string $propertyName,
        mixed $value,
        array $values,
        ?Context $ctx,
    ): void;
}
