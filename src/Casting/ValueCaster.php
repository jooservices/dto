<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use ReflectionException;

final class ValueCaster
{
    public function __construct(
        private readonly ValueTypeCaster $typeCaster,
        private readonly AttributeCastResolver $attributes = new AttributeCastResolver(),
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
    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        if ($value === null && ($property->allowsNull || $property->type->allowsNull())) {
            return null;
        }

        $castWith = $this->attributes->castWith($property, $value, $ctx);
        if ($castWith !== null) {
            return $castWith;
        }

        $strict = $this->attributes->findStrictType($property);
        if ($strict !== null) {
            if ($this->typeCaster->isExactMatch($property->type, $value)) {
                return $value;
            }

            throw $this->attributes->strictFailure($property, $value, $strict);
        }

        return $this->typeCaster->cast($property->type, $property, $value, $ctx);
    }
}
