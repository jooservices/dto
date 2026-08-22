<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Support\CachedOptionedInstantiator;
use ReflectionException;

final class CastWithExecutor
{
    public function __construct(
        private readonly CachedOptionedInstantiator $instantiator = new CachedOptionedInstantiator(),
    ) {
    }

    /**
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function execute(CastWith $attribute, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        $caster = $this->instantiator->make($attribute->casterClass, $attribute->options);
        if (! $caster instanceof CasterInterface) {
            throw new CastException(
                message: 'CastWith class must implement CasterInterface.',
                path: $property->name,
            );
        }

        return $caster->cast($property->type, $property, $value, $ctx);
    }
}
