<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use BackedEnum;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use UnitEnum;
use ValueError;

final class EnumCaster implements CasterInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        if (! $property->type->isEnum) {
            return false;
        }

        if ($value instanceof UnitEnum) {
            return true;
        }

        return is_string($value) || is_int($value);
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): ?UnitEnum
    {
        $enumClass = $property->type->enumClass;
        $isPermissive = $ctx?->isPermissiveMode() ?? false;

        if ($enumClass === null) {
            if ($isPermissive) {
                return null;
            }

            throw CastException::cannotCast($value, 'enum', $property->name);
        }

        if ($value instanceof $enumClass) {
            /** @var UnitEnum $value */
            return $value;
        }

        if ($isPermissive) {
            try {
                if ($property->type->isBackedEnum()) {
                    /** @var class-string<BackedEnum> $enumClass */
                    return $this->castToBackedEnum($value, $enumClass, $property);
                }

                /** @var class-string<UnitEnum> $enumClass */
                return $this->castToUnitEnum($value, $enumClass, $property);
            } catch (CastException) { // @phpstan-ignore catch.neverThrown (methods above throw CastException)
                return null;
            }
        }

        if ($property->type->isBackedEnum()) {
            /** @var class-string<BackedEnum> $enumClass */
            return $this->castToBackedEnum($value, $enumClass, $property);
        }

        /** @var class-string<UnitEnum> $enumClass */
        return $this->castToUnitEnum($value, $enumClass, $property);
    }

    /**
     * @param  class-string<BackedEnum>  $enumClass
     */
    private function castToBackedEnum(mixed $value, string $enumClass, PropertyMeta $property): BackedEnum
    {
        if (! is_string($value) && ! is_int($value)) {
            throw CastException::invalidEnumValue($value, $enumClass, $property->name);
        }

        try {
            return $enumClass::from($value);
        } catch (ValueError) {
            throw CastException::invalidEnumValue($value, $enumClass, $property->name);
        }
    }

    /**
     * @param  class-string<UnitEnum>  $enumClass
     */
    private function castToUnitEnum(mixed $value, string $enumClass, PropertyMeta $property): UnitEnum
    {
        if (! is_string($value)) {
            throw CastException::invalidEnumValue($value, $enumClass, $property->name);
        }

        $cases = $enumClass::cases();

        foreach ($cases as $case) {
            if ($case->name === $value) {
                return $case;
            }
        }

        throw CastException::invalidEnumValue($value, $enumClass, $property->name);
    }
}
