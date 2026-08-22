<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

final class HydrationWorkflow
{
    public function __construct(
        private readonly HydrationRunner $runner,
        private readonly FromStateBuilder $fromStateBuilder,
        private readonly HydrationDepthGuard $depthGuard = new HydrationDepthGuard(),
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws \JOOservices\Dto\Exceptions\CastException
     * @throws \JOOservices\Dto\Exceptions\MappingException
     * @throws \JOOservices\Dto\Exceptions\ValidationException
     * @throws ReflectionException
     */
    public function hydrate(ClassMeta $meta, array $data, Context $ctx): object
    {
        $this->depthGuard->assertWithinLimit($ctx);

        if (! $meta->hasConstructor) {
            throw new HydrationException(
                message: HydrationException::CONSTRUCTOR_REQUIRED,
                expectedType: $meta->className,
            );
        }

        return $this->runner->run($meta, $data, $ctx);
    }

    /**
     * @param  array<string, mixed>  $state
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws \JOOservices\Dto\Exceptions\ValidationException
     */
    public function fromState(ClassMeta $meta, array $state, Context $ctx): object
    {
        return $this->fromStateBuilder->build($meta, $state, $ctx);
    }
}
