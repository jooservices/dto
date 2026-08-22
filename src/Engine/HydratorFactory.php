<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Hydration\FromStateBuilder;
use JOOservices\Dto\Hydration\HydrationInstantiator;
use JOOservices\Dto\Hydration\Hydrator;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Validation\ObjectValidator;

final class HydratorFactory
{
    public function create(
        MetaFactory $metaFactory,
        ObjectValidator $validator,
        SharedDefaults $defaults,
    ): Hydrator {
        $casters = (new CasterBootstrap())->create($defaults);
        $instantiator = new HydrationInstantiator();
        $fromStateBuilder = new FromStateBuilder($instantiator, $validator);
        $workflowFactory = new HydrationWorkflowFactory();

        return new Hydrator(
            $workflowFactory->create($metaFactory, $validator, $casters, new Mapper(), $fromStateBuilder),
            $casters,
            $defaults,
        );
    }
}
