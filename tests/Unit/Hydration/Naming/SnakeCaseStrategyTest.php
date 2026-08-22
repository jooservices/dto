<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Naming;

use JOOservices\Dto\Hydration\Naming\CamelCaseStrategy;
use JOOservices\Dto\Hydration\Naming\SnakeCaseStrategy;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @group integration
 */
#[Group('integration')]
final class SnakeCaseStrategyTest extends TestCase
{
    public function testToPropertyConvertsSnakeCaseToCamelCase(): void
    {
        $strategy = new SnakeCaseStrategy();

        self::assertSame('userName', $strategy->toProperty('user_name'));
    }

    public function testToSourceConvertsCamelCaseToSnakeCase(): void
    {
        $strategy = new SnakeCaseStrategy();

        self::assertSame('user_name', $strategy->toSource('userName'));
    }

    /**
     * Weird/edge finding: SnakeCaseStrategy delegates both directions to CamelCaseStrategy
     * verbatim — the class has no behavior of its own distinct from its sibling.
     */
    public function testBehavesIdenticallyToCamelCaseStrategy(): void
    {
        $snake = new SnakeCaseStrategy();
        $camel = new CamelCaseStrategy();

        self::assertSame($camel->toProperty('order_line_item'), $snake->toProperty('order_line_item'));
        self::assertSame($camel->toSource('orderLineItem'), $snake->toSource('orderLineItem'));
    }
}
