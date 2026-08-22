<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

interface ValidatorRegistryInterface
{
    public function register(ValidatorInterface $validator): void;

    /**
     * @param  array<string, mixed>  $values
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validateProperty(
        ClassMeta $meta,
        string $propertyName,
        mixed $value,
        array $values,
        ?Context $ctx,
    ): void;
}
