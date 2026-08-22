<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Engine\EngineFactory;
use JOOservices\Dto\Engine\EngineHolder;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Tests\TestCase;

final class EngineHolderTest extends TestCase
{
    protected function tearDown(): void
    {
        EngineHolder::reset();
        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testSharedEngineIsReusableUntilReset(): void
    {
        $first = EngineHolder::get();
        $second = EngineHolder::get();
        self::assertSame($first, $second);

        EngineHolder::reset();
        $third = EngineHolder::get();
        self::assertNotSame($first, $third);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     */
    public function testFactoryCreatesEngine(): void
    {
        $engine = EngineFactory::createDefault();
        self::assertSame([], $engine->normalizeInput([]));
    }
}
