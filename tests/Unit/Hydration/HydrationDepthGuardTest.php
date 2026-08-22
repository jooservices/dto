<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\HydrationDepthGuard;
use JOOservices\Dto\Tests\TestCase;

final class HydrationDepthGuardTest extends TestCase
{
    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     */
    public function testAllowsDepthEqualToTheDefaultMaximum(): void
    {
        $ctx = (new Context(false, false))->withCustomData(['hydrationDepth' => 32]);

        (new HydrationDepthGuard())->assertWithinLimit($ctx);

        self::addToAssertionCount(1);
    }

    /**
     * C11: one level beyond the default limit must fail loud.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     */
    public function testRejectsDepthBeyondTheDefaultMaximum(): void
    {
        $ctx = (new Context(false, false))->withCustomData(['hydrationDepth' => 33]);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Hydration depth exceeded.');

        (new HydrationDepthGuard())->assertWithinLimit($ctx);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     */
    public function testHonoursACustomMaxHydrationDepth(): void
    {
        $ctx = (new Context(false, false))->withCustomData([
            'hydrationDepth' => 5,
            'maxHydrationDepth' => 4,
        ]);

        $this->expectException(HydrationException::class);

        (new HydrationDepthGuard())->assertWithinLimit($ctx);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     */
    public function testDefaultsDepthToZeroWhenAbsentFromCustomData(): void
    {
        (new HydrationDepthGuard())->assertWithinLimit(new Context(false, false));

        self::addToAssertionCount(1);
    }
}
