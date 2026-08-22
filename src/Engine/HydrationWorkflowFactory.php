<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Casting\ValueCaster;
use JOOservices\Dto\Hydration\DiscriminatorResolver;
use JOOservices\Dto\Hydration\FromStateBuilder;
use JOOservices\Dto\Hydration\HydrationMappedStateResolver;
use JOOservices\Dto\Hydration\HydrationRunner;
use JOOservices\Dto\Hydration\HydrationWorkflow;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Hydration\UnknownKeyGuard;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Validation\ObjectValidator;

final class HydrationWorkflowFactory
{
    public function create(
        MetaFactory $metaFactory,
        ObjectValidator $validator,
        ValueCaster $casters,
        Mapper $mapper,
        FromStateBuilder $fromStateBuilder,
    ): HydrationWorkflow {
        $mappedState = new HydrationMappedStateResolver(
            $mapper,
            new DiscriminatorResolver($metaFactory),
            new UnknownKeyGuard($mapper),
        );

        return new HydrationWorkflow(
            new HydrationRunner(
                $mappedState,
                (new HydrationPipelineFactory())->create($casters, $validator),
            ),
            $fromStateBuilder,
        );
    }
}
