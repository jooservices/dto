<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Exception;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;

final class DateTimeCaster implements CasterInterface
{
    private const string DEFAULT_FORMAT = 'Y-m-d\TH:i:sP';

    public function __construct(
        private readonly string $format = self::DEFAULT_FORMAT,
    ) {}

    public function supports(PropertyMeta $property, mixed $value): bool
    {
        if (! $property->type->isDateTime) {
            return false;
        }

        if ($value instanceof DateTimeInterface) {
            return true;
        }

        return is_string($value) || is_int($value);
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): ?DateTimeInterface
    {
        $targetType = $property->type->name;
        $isPermissive = $ctx?->isPermissiveMode() ?? false;

        if ($isPermissive) {
            try {
                if ($value instanceof DateTimeInterface) {
                    return $this->convertToTargetType($value, $targetType);
                }

                if (is_int($value)) {
                    return $this->createFromTimestamp($value, $targetType, $property);
                }

                if (is_string($value)) {
                    return $this->createFromString($value, $targetType, $property);
                }

                return null;
            } catch (CastException) { // @phpstan-ignore catch.neverThrown (methods above throw CastException)
                return null;
            }
        }

        if ($value instanceof DateTimeInterface) {
            return $this->convertToTargetType($value, $targetType);
        }

        if (is_int($value)) {
            return $this->createFromTimestamp($value, $targetType, $property);
        }

        if (is_string($value)) {
            return $this->createFromString($value, $targetType, $property);
        }

        throw CastException::cannotCast($value, $targetType, $property->name);
    }

    private function convertToTargetType(DateTimeInterface $dateTime, string $targetType): DateTimeInterface
    {
        if ($targetType === DateTimeImmutable::class && ! $dateTime instanceof DateTimeImmutable) {
            return DateTimeImmutable::createFromInterface($dateTime);
        }

        if ($targetType === DateTime::class && ! $dateTime instanceof DateTime) {
            return DateTime::createFromInterface($dateTime);
        }

        return $dateTime;
    }

    private function createFromTimestamp(int $timestamp, string $targetType, PropertyMeta $property): DateTimeInterface
    {
        if ($targetType === DateTimeImmutable::class || $targetType === DateTimeInterface::class) {
            $result = DateTimeImmutable::createFromFormat('U', (string) $timestamp);
        } else {
            $result = DateTime::createFromFormat('U', (string) $timestamp);
        }

        if ($result === false) {
            throw CastException::cannotCast($timestamp, $targetType, $property->name);
        }

        return $result;
    }

    private function createFromString(string $value, string $targetType, PropertyMeta $property): DateTimeInterface
    {
        $result = $this->tryCreateFromFormat($value, $targetType);

        if ($result !== null) {
            return $result;
        }

        $result = $this->tryCreateFromString($value, $targetType);

        if ($result !== null) {
            return $result;
        }

        throw CastException::invalidDateTimeFormat($value, $this->format, $property->name);
    }

    private function tryCreateFromFormat(string $value, string $targetType): ?DateTimeInterface
    {
        if ($targetType === DateTimeImmutable::class || $targetType === DateTimeInterface::class) {
            $result = DateTimeImmutable::createFromFormat($this->format, $value);
        } else {
            $result = DateTime::createFromFormat($this->format, $value);
        }

        return $result !== false ? $result : null;
    }

    private function tryCreateFromString(string $value, string $targetType): ?DateTimeInterface
    {
        try {
            if ($targetType === DateTimeImmutable::class || $targetType === DateTimeInterface::class) {
                return new DateTimeImmutable($value);
            }

            return new DateTime($value);
        } catch (Exception) {
            return null;
        }
    }
}
