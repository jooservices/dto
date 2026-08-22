<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use BackedEnum;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use UnitEnum;

final class EnumCaster implements CasterInterface
{
    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($property, $ctx, $value);

        return $type->kind === TypeDescriptor::KIND_ENUM;
    }

    /**
     * @throws CastException
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        unset($ctx);
        $class = $type->className;
        if ($class === null || ! is_a($class, UnitEnum::class, true)) {
            throw new CastException(
                message: 'Enum class is not defined.',
                path: $property->name,
            );
        }

        if ($value instanceof $class) {
            return $value;
        }

        if (is_a($class, BackedEnum::class, true) && (is_string($value) || is_int($value))) {
            $enum = $class::tryFrom($value);
            if ($enum instanceof $class) {
                return $enum;
            }
        }

        throw new CastException(
            message: 'Unable to cast enum value.',
            path: $property->name,
            expectedType: $class,
            givenType: get_debug_type($value),
            givenValue: $property->redactWith ?? $value,
        );
    }
}
