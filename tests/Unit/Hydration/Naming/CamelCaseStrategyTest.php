<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Naming;

use JOOservices\Dto\Hydration\Naming\CamelCaseStrategy;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @group integration
 */
#[Group('integration')]
final class CamelCaseStrategyTest extends TestCase
{
    public function testToPropertyConvertsSnakeCaseToCamelCase(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('userName', $strategy->toProperty('user_name'));
        self::assertSame('fullLegalName', $strategy->toProperty('full_legal_name'));
    }

    public function testToPropertyLeavesAlreadyCamelKeysUnchanged(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('name', $strategy->toProperty('name'));
    }

    public function testToPropertyOnEmptyStringReturnsEmptyString(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('', $strategy->toProperty(''));
    }

    public function testToSourceConvertsCamelCaseToSnakeCase(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('user_name', $strategy->toSource('userName'));
        self::assertSame('full_legal_name', $strategy->toSource('fullLegalName'));
    }

    public function testToSourceLeavesAlreadyLowercaseKeysUnchanged(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('name', $strategy->toSource('name'));
    }

    /**
     * Weird/edge: a leading-uppercase property name still normalizes cleanly because the
     * leading underscore introduced by the regex substitution is stripped.
     */
    public function testToSourceStripsALeadingUnderscoreFromAnUppercaseFirstLetter(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('user_name', $strategy->toSource('UserName'));
    }

    public function testRoundTripsBackToTheOriginalKey(): void
    {
        $strategy = new CamelCaseStrategy();

        self::assertSame('order_line_item', $strategy->toSource($strategy->toProperty('order_line_item')));
    }
}
