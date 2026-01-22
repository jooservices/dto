<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use Throwable;

class CastException extends JdtoException
{
    public static function cannotCast(
        mixed $value,
        string $targetType,
        string $path = '',
        ?Throwable $previous = null,
    ): self {
        return new self(
            message: "Cannot cast value to '{$targetType}'",
            path: $path,
            expectedType: $targetType,
            givenType: get_debug_type($value),
            givenValue: $value,
            previous: $previous,
        );
    }

    public static function invalidEnumValue(
        mixed $value,
        string $enumClass,
        string $path = '',
    ): self {
        return new self(
            message: "Invalid value for enum '{$enumClass}'",
            path: $path,
            expectedType: $enumClass,
            givenType: get_debug_type($value),
            givenValue: $value,
        );
    }

    public static function invalidDateTimeFormat(
        mixed $value,
        string $format,
        string $path = '',
    ): self {
        return new self(
            message: "Cannot parse date/time value with format '{$format}'",
            path: $path,
            expectedType: 'DateTimeImmutable',
            givenType: get_debug_type($value),
            givenValue: $value,
        );
    }

    public static function noCasterFound(
        mixed $value,
        string $targetType,
        string $path = '',
    ): self {
        return new self(
            message: "No caster found for type '{$targetType}'",
            path: $path,
            expectedType: $targetType,
            givenType: get_debug_type($value),
            givenValue: $value,
        );
    }
}
