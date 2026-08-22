<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use DateTimeImmutable;
use DateTimeInterface;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Casting\DateTimeFormatParser;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use ValueError;

final class DateTimeCaster implements CasterInterface
{
    /**
     * Unambiguous fallback formats tried when {@see self::$format} does not match.
     * Deliberately excludes loose/relative parsing (e.g. `strtotime("next thursday")`)
     * to avoid silently accepting garbage input (C8 regression).
     *
     * @var list<string>
     */
    private const array FALLBACK_FORMATS = [
        DateTimeInterface::ATOM,
        DateTimeInterface::RFC3339,
        DateTimeInterface::RFC3339_EXTENDED,
        'Y-m-d\TH:i:sP',
        'Y-m-d\TH:i:s.uP',
        'Y-m-d H:i:s',
        'Y-m-d',
    ];

    public function __construct(
        private readonly string $format = DateTimeInterface::ATOM,
        private readonly DateTimeFormatParser $parser = new DateTimeFormatParser(),
    ) {
    }

    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($property, $ctx, $value);
        $class = $type->className;

        return $type->kind === TypeDescriptor::KIND_CLASS
            && $class !== null
            && is_a($class, DateTimeInterface::class, true);
    }

    /**
     * @throws CastException
     * @throws ValueError
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        unset($type, $ctx);
        if ($value instanceof DateTimeInterface) {
            return $value;
        }

        if (is_int($value)) {
            return (new DateTimeImmutable())->setTimestamp($value);
        }

        if (! is_string($value) || $value === '') {
            throw $this->invalidValue($property, $value);
        }

        return $this->parseString($property, $value);
    }

    /**
     * @throws CastException
     * @throws ValueError
     */
    private function parseString(PropertyMeta $property, string $value): DateTimeImmutable
    {
        $parsed = $this->parser->parse($this->format, $value);
        if ($parsed instanceof DateTimeImmutable) {
            return $parsed;
        }

        foreach (self::FALLBACK_FORMATS as $format) {
            if ($format === $this->format) {
                continue;
            }

            $parsed = $this->parser->parse($format, $value);
            if ($parsed instanceof DateTimeImmutable) {
                return $parsed;
            }
        }

        throw new CastException(
            message: 'Unable to parse DateTime value.',
            path: $property->name,
            expectedType: DateTimeInterface::class,
            givenType: 'string',
            givenValue: $property->redactWith ?? $value,
        );
    }

    private function invalidValue(PropertyMeta $property, mixed $value): CastException
    {
        return new CastException(
            message: 'Unable to cast DateTime value.',
            path: $property->name,
            expectedType: DateTimeInterface::class,
            givenType: get_debug_type($value),
            givenValue: $property->redactWith ?? $value,
        );
    }
}
