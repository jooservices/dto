<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionException;

interface NestedDtoFactory
{
    /**
     * @param  class-string  $className
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function create(string $className, mixed $source, ?Context $ctx): object;
}
