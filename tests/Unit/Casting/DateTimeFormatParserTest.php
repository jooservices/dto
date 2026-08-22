<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use DateTimeImmutable;
use JOOservices\Dto\Casting\DateTimeFormatParser;
use JOOservices\Dto\Tests\TestCase;
use ValueError;

final class DateTimeFormatParserTest extends TestCase
{
    /**
     * @throws ValueError
     */
    public function testParsesNonUtcOffsetWithoutNormalizingAwayFromInput(): void
    {
        $parser = new DateTimeFormatParser();
        $parsed = $parser->parse(DateTimeImmutable::ATOM, '2026-01-01T10:00:00+07:00');

        self::assertInstanceOf(DateTimeImmutable::class, $parsed);
        self::assertSame('2026-01-01T10:00:00+07:00', $parsed->format(DateTimeImmutable::ATOM));
    }

    /**
     * @throws ValueError
     */
    public function testParsesZuluSuffix(): void
    {
        $parser = new DateTimeFormatParser();
        $parsed = $parser->parse(DateTimeImmutable::ATOM, '2026-01-01T10:00:00Z');

        self::assertInstanceOf(DateTimeImmutable::class, $parsed);
        self::assertSame('2026-01-01T10:00:00+00:00', $parsed->format(DateTimeImmutable::ATOM));
    }

    /**
     * @throws ValueError
     */
    public function testRejectsTrailingGarbage(): void
    {
        $parser = new DateTimeFormatParser();
        $parsed = $parser->parse(DateTimeImmutable::ATOM, '2026-01-01T10:00:00+00:00 garbage');

        self::assertNull($parsed);
    }
}
