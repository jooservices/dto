<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use ReflectionException;

interface MetaFactoryInterface
{
    /**
     * @throws \JOOservices\Dto\Exceptions\HydrationException
     * @throws ReflectionException
     */
    public function create(string $className): ClassMeta;
}
