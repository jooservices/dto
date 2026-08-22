<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Casting\ValueCaster;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;

final class Hydrator implements HydratorInterface
{
    public function __construct(
        private readonly HydrationWorkflow $workflow,
        private readonly ValueCaster $valueCaster,
        private readonly SharedDefaults $defaults,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function hydrate(ClassMeta $meta, array $data, ?Context $ctx): object
    {
        return $this->workflow->hydrate($meta, $data, $ctx ?? $this->defaults->looseContext());
    }

    /**
     * @param  array<string, mixed>  $state
     */
    public function fromState(ClassMeta $meta, array $state, ?Context $ctx): object
    {
        return $this->workflow->fromState($meta, $state, $ctx ?? $this->defaults->looseContext());
    }

    public function castPatched(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        return $this->valueCaster->cast($property, $value, $ctx);
    }
}
