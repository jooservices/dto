<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Casting\Casters\BoolCaster;
use JOOservices\Dto\Casting\Casters\DateTimeCaster;
use JOOservices\Dto\Casting\Casters\EnumCaster;
use JOOservices\Dto\Casting\Casters\FloatCaster;
use JOOservices\Dto\Casting\Casters\IntCaster;
use JOOservices\Dto\Casting\Casters\StringCaster;
use JOOservices\Dto\Casting\ValueCaster;
use JOOservices\Dto\Casting\ValueTypeCaster;
use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Hydration\ClassFromNestedDtoFactory;

final class CasterBootstrap
{
    public function create(SharedDefaults $defaults): ValueCaster
    {
        $casters = new CasterRegistry();
        $casters->register(new IntCaster());
        $casters->register(new FloatCaster());
        $casters->register(new StringCaster());
        $casters->register(new BoolCaster());
        $casters->register(new EnumCaster());
        $casters->register(new DateTimeCaster());

        $nestedDtos = new ClassFromNestedDtoFactory($defaults);

        return new ValueCaster(new ValueTypeCaster($casters, $nestedDtos));
    }
}
