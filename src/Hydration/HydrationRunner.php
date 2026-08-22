<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

final class HydrationRunner
{
    public function __construct(
        private readonly HydrationMappedStateResolver $mappedState,
        private readonly HydrationInstanceBuilder $instanceBuilder,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws \JOOservices\Dto\Exceptions\CastException
     * @throws \JOOservices\Dto\Exceptions\HydrationException
     * @throws InvalidArgumentException
     * @throws \JOOservices\Dto\Exceptions\MappingException
     * @throws ReflectionException
     * @throws \JOOservices\Dto\Exceptions\ValidationException
     */
    public function run(ClassMeta $meta, array $data, Context $ctx): object
    {
        $state = $this->mappedState->resolve($meta, $data, $ctx);

        return $this->instanceBuilder->build($state['meta'], $state['mapped'], $ctx);
    }
}
