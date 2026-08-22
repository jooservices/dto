<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\StrictType;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use ReflectionException;

final class AttributeCastResolver
{
    public function __construct(
        private readonly CastWithExecutor $castWith = new CastWithExecutor(),
    ) {
    }

    /**
     * @return mixed|null
     *
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function castWith(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        $attribute = $property->resolved->castWith;
        if ($attribute instanceof CastWith) {
            return $this->castWith->execute($attribute, $property, $value, $ctx);
        }

        return null;
    }

    public function findStrictType(PropertyMeta $property): ?StrictType
    {
        return $property->resolved->strictType;
    }

    public function strictFailure(PropertyMeta $property, mixed $value, StrictType $attribute): CastException
    {
        return new CastException(
            message: $attribute->message,
            path: $property->name,
            expectedType: $property->type->className ?? $property->type->builtin,
            givenType: get_debug_type($value),
            givenValue: $property->redactWith ?? $value,
        );
    }
}
