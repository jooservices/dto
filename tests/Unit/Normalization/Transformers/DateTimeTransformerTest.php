<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization\Transformers;

use DateTimeImmutable;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @group integration
 */
#[Group('integration')]
final class DateTimeTransformerTest extends TestCase
{
    public function testSupportsOnlyDateTimeInterfaceValues(): void
    {
        $transformer = new DateTimeTransformer();

        self::assertTrue($transformer->supports(new DateTimeImmutable('2026-01-01T00:00:00+00:00')));
        self::assertFalse($transformer->supports('2026-01-01'));
        self::assertFalse($transformer->supports(null));
    }

    public function testTransformFormatsUsingAtomFormat(): void
    {
        $transformer = new DateTimeTransformer();
        $date = new DateTimeImmutable('2026-03-15T12:30:00+02:00');

        self::assertSame('2026-03-15T12:30:00+02:00', $transformer->transform($date));
    }

    public function testTransformPassesThroughNonDateTimeValuesUnchanged(): void
    {
        $transformer = new DateTimeTransformer();

        self::assertSame('plain-string', $transformer->transform('plain-string'));
    }
}
