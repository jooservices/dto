<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;
use ReflectionException;

interface HydratorInterface
{
    /**
     * @param  array<string, mixed>  $data
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function hydrate(ClassMeta $meta, array $data, ?Context $ctx): object;

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function castPatched(PropertyMeta $property, mixed $value, ?Context $ctx): mixed;

    /**
     * @param  array<string, mixed>  $state
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function fromState(ClassMeta $meta, array $state, ?Context $ctx): object;
}
