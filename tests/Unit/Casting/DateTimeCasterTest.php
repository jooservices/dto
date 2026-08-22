<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use DateTimeImmutable;
use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\EventDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class DateTimeCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsAtomFormattedString(): void
    {
        $dto = EventDto::fromArray(['startedAt' => '2026-01-01T10:00:00+00:00']);

        self::assertSame('2026-01-01T10:00:00+00:00', $dto->startedAt->format(DateTimeImmutable::ATOM));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsUnambiguousFallbackFormat(): void
    {
        $dto = EventDto::fromArray(['startedAt' => '2026-01-01 10:00:00']);

        self::assertSame('2026-01-01', $dto->startedAt->format('Y-m-d'));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsIntegerTimestamp(): void
    {
        $dto = EventDto::fromArray(['startedAt' => 1735689600]);

        self::assertSame(1735689600, $dto->startedAt->getTimestamp());
    }

    /**
     * C8 regression: relative/natural-language strings must never be silently
     * accepted through a loose strtotime()-style fallback.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsRelativeNaturalLanguageStrings(): void
    {
        $this->expectException(CastException::class);
        EventDto::fromArray(['startedAt' => 'next thursday']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsGarbageTrailingCharacters(): void
    {
        $this->expectException(CastException::class);
        EventDto::fromArray(['startedAt' => '2026-01-01T10:00:00+00:00 garbage']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsEmptyString(): void
    {
        $this->expectException(CastException::class);
        EventDto::fromArray(['startedAt' => '']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsNonUtcOffsetEquivalentToInput(): void
    {
        $dto = EventDto::fromArray(['startedAt' => '2026-01-01T10:00:00+07:00']);

        self::assertSame('2026-01-01T10:00:00+07:00', $dto->startedAt->format(DateTimeImmutable::ATOM));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsZuluSuffix(): void
    {
        $dto = EventDto::fromArray(['startedAt' => '2026-01-01T10:00:00Z']);

        self::assertSame('2026-01-01T10:00:00+00:00', $dto->startedAt->format(DateTimeImmutable::ATOM));
    }
}
