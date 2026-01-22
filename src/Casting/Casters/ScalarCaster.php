<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;

final class ScalarCaster implements CasterInterface
{
    private const array SUPPORTED_TYPES = ['int', 'float', 'string', 'bool'];

    public function supports(PropertyMeta $property, mixed $value): bool
    {
        if (! $property->type->isScalar()) {
            return false;
        }

        return $this->canCastToType($property->type->name, $value);
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): bool|float|int|string|null
    {
        $targetType = $property->type->name;
        $isPermissive = $ctx?->isPermissiveMode() ?? false;

        if ($isPermissive) {
            try {
                return match ($targetType) {
                    'int' => $this->castToInt($value, $property),
                    'float' => $this->castToFloat($value, $property),
                    'string' => $this->castToString($value, $property),
                    'bool' => $this->castToBool($value, $property),
                    default => null,
                };
            } catch (CastException) { // @phpstan-ignore catch.neverThrown (methods above throw CastException)
                return null;
            }
        }

        return match ($targetType) {
            'int' => $this->castToInt($value, $property),
            'float' => $this->castToFloat($value, $property),
            'string' => $this->castToString($value, $property),
            'bool' => $this->castToBool($value, $property),
            default => throw CastException::cannotCast($value, $targetType, $property->name),
        };
    }

    private function canCastToType(string $type, mixed $value): bool
    {
        if (! in_array($type, self::SUPPORTED_TYPES, true)) {
            return false;
        }

        return match ($type) {
            'int', 'float' => is_numeric($value) || is_bool($value),
            'string', 'bool' => is_scalar($value) || $value === null,
        };
    }

    private function castToInt(mixed $value, PropertyMeta $property): int
    {
        if (is_int($value)) {
            return $value;
        }

        if (is_float($value)) {
            return (int) $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }

        throw CastException::cannotCast($value, 'int', $property->name);
    }

    private function castToFloat(mixed $value, PropertyMeta $property): float
    {
        if (is_float($value)) {
            return $value;
        }

        if (is_int($value)) {
            return (float) $value;
        }

        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        throw CastException::cannotCast($value, 'float', $property->name);
    }

    private function castToString(mixed $value, PropertyMeta $property): string
    {
        if (is_string($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if ($value === null) {
            return '';
        }

        throw CastException::cannotCast($value, 'string', $property->name);
    }

    private function castToBool(mixed $value, PropertyMeta $property): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return $value !== 0 && $value !== 0.0;
        }

        if (is_string($value)) {
            $lower = strtolower($value);

            if (in_array($lower, ['true', '1', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($lower, ['false', '0', 'no', 'off', ''], true)) {
                return false;
            }

            throw CastException::cannotCast($value, 'bool', $property->name);
        }

        if ($value === null) {
            return false;
        }

        throw CastException::cannotCast($value, 'bool', $property->name);
    }
}
