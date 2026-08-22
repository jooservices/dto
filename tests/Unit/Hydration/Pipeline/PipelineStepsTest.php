<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Pipeline;

use JOOservices\Dto\Hydration\Pipeline\Lowercase;
use JOOservices\Dto\Hydration\Pipeline\TrimStrings;
use JOOservices\Dto\Hydration\Pipeline\Uppercase;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @group integration
 */
#[Group('integration')]
final class PipelineStepsTest extends TestCase
{
    public function testLowercaseLowersStringValues(): void
    {
        self::assertSame('abc', (new Lowercase())->handle('ABC'));
    }

    public function testLowercasePassesThroughNonStringValuesUnchanged(): void
    {
        self::assertSame(42, (new Lowercase())->handle(42));
        self::assertNull((new Lowercase())->handle(null));
        self::assertSame(['A'], (new Lowercase())->handle(['A']));
    }

    public function testUppercaseUppersStringValues(): void
    {
        self::assertSame('ABC', (new Uppercase())->handle('abc'));
    }

    public function testUppercasePassesThroughNonStringValuesUnchanged(): void
    {
        self::assertSame(42, (new Uppercase())->handle(42));
        self::assertSame(3.5, (new Uppercase())->handle(3.5));
    }

    public function testTrimStringsTrimsLeadingAndTrailingWhitespace(): void
    {
        self::assertSame('abc', (new TrimStrings())->handle('  abc  '));
    }

    public function testTrimStringsPassesThroughNonStringValuesUnchanged(): void
    {
        self::assertSame(42, (new TrimStrings())->handle(42));
        self::assertTrue((new TrimStrings())->handle(true));
    }
}
