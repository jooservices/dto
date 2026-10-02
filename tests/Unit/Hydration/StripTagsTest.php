<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use DOMException;
use JOOservices\Dto\Hydration\Pipeline\StripTags;
use JOOservices\Dto\Tests\TestCase;

final class StripTagsTest extends TestCase
{
    /**
     * @throws DOMException When an attribute cannot be removed.
     */
    public function testRemovesDisallowedTagsEntirely(): void
    {
        $step = new StripTags();

        self::assertSame('alert(1)', $step->handle('<script>alert(1)</script>'));
    }

    /**
     * @throws DOMException When an attribute cannot be removed.
     */
    public function testNonStringValuesPassThroughUnchanged(): void
    {
        $step = new StripTags();

        self::assertSame(42, $step->handle(42));
        self::assertNull($step->handle(null));
    }

    /**
     * @throws DOMException When an attribute cannot be removed.
     */
    public function testKeepsAllowedTagButStripsItsAttributes(): void
    {
        $step = new StripTags('<b>');

        self::assertSame('<b>hello</b>', $step->handle('<b onclick="alert(1)">hello</b>'));
    }

    /**
     * S2 regression: an attribute value containing a literal `>` must not let a
     * regex-based stripper truncate the match early and leave a live attribute
     * (or its payload) sitting next to the tag.
     *
     * @throws DOMException When an attribute cannot be removed.
     */
    public function testAttributeContainingGreaterThanCannotSurviveAsALiveAttribute(): void
    {
        $step = new StripTags('<b>');

        $result = $step->handle('<b title="a>b" onmouseover="alert(1)">hover</b>');

        self::assertIsString($result);
        self::assertStringNotContainsString('onmouseover', $result);
        self::assertStringNotContainsString('alert(1)', $result);
    }

    /**
     * @throws DOMException When an attribute cannot be removed.
     */
    public function testEmptyStringIsUnchanged(): void
    {
        $step = new StripTags('<b>');

        self::assertSame('', $step->handle(''));
    }
}
