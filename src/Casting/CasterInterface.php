<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use ReflectionException;

interface CasterInterface
{
    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool;

    /**
     * @throws \JOOservices\Dto\Exceptions\CastException
     * @throws InvalidArgumentException
     * @throws \JOOservices\Dto\Exceptions\HydrationException
     * @throws \JOOservices\Dto\Exceptions\MappingException
     * @throws ReflectionException
     * @throws \JOOservices\Dto\Exceptions\ValidationException
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed;
}
