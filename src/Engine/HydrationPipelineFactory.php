<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Casting\ValueCaster;
use JOOservices\Dto\Hydration\DefaultFromResolver;
use JOOservices\Dto\Hydration\HydrationArgumentsBuilder;
use JOOservices\Dto\Hydration\HydrationInstanceBuilder;
use JOOservices\Dto\Hydration\HydrationInstantiator;
use JOOservices\Dto\Hydration\PipelineRunner;
use JOOservices\Dto\Hydration\PropertyValuePreparer;
use JOOservices\Dto\Validation\ObjectValidator;

final class HydrationPipelineFactory
{
    public function create(ValueCaster $casters, ObjectValidator $validator): HydrationInstanceBuilder
    {
        return new HydrationInstanceBuilder(
            new HydrationArgumentsBuilder(
                new DefaultFromResolver(),
                new PropertyValuePreparer(new PipelineRunner(), $casters),
            ),
            new HydrationInstantiator(),
            $validator,
        );
    }
}
