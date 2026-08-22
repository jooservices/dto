<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Support;

use InvalidArgumentException;
use JOOservices\Dto\Support\CachedOptionedInstantiator;
use JOOservices\Dto\Support\DtoFingerprint;
use JOOservices\Dto\Tests\Fixtures\MutableCounterPipelineStep;
use JOOservices\Dto\Tests\Fixtures\NonCloneablePipelineStep;
use JOOservices\Dto\Tests\TestCase;

final class CachedOptionedInstantiatorTest extends TestCase
{
    protected function setUp(): void
    {
        CachedOptionedInstantiator::reset();
        parent::setUp();
    }

    protected function tearDown(): void
    {
        CachedOptionedInstantiator::reset();
        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testReturnsFreshInstanceFromCachedPrototype(): void
    {
        CachedOptionedInstantiator::reset();
        $instantiator = new CachedOptionedInstantiator();
        $first = $instantiator->make(DtoFingerprint::class);
        $second = $instantiator->make(DtoFingerprint::class);

        self::assertNotSame($first, $second);
        self::assertSame(1, CachedOptionedInstantiator::getCachedCount());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMutableInstancesDoNotShareState(): void
    {
        $instantiator = new CachedOptionedInstantiator();
        $first = $instantiator->make(MutableCounterPipelineStep::class);
        $second = $instantiator->make(MutableCounterPipelineStep::class);

        self::assertInstanceOf(MutableCounterPipelineStep::class, $first);
        self::assertInstanceOf(MutableCounterPipelineStep::class, $second);
        $first->handle('a');
        $second->handle('b');

        self::assertSame(1, $first->invocations());
        self::assertSame(1, $second->invocations());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testEvictsOldestEntryWhenMaxEntriesExceeded(): void
    {
        CachedOptionedInstantiator::configureMaxEntries(1);
        $instantiator = new CachedOptionedInstantiator();

        $first = $instantiator->make(CachedOptionedInstantiator::class);
        $instantiator->make(DtoFingerprint::class);

        self::assertSame(1, CachedOptionedInstantiator::getCachedCount());
        self::assertNotSame($first, $instantiator->make(CachedOptionedInstantiator::class));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testNonCloneableClassCreatesFreshInstanceWithoutCaching(): void
    {
        $instantiator = new CachedOptionedInstantiator();
        $first = $instantiator->make(NonCloneablePipelineStep::class);
        $second = $instantiator->make(NonCloneablePipelineStep::class);

        self::assertNotSame($first, $second);
        self::assertSame(0, CachedOptionedInstantiator::getCachedCount());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testResetClearsCachedInstances(): void
    {
        (new CachedOptionedInstantiator())->make(CachedOptionedInstantiator::class);
        self::assertSame(1, CachedOptionedInstantiator::getCachedCount());

        CachedOptionedInstantiator::reset();

        self::assertSame(0, CachedOptionedInstantiator::getCachedCount());
    }
}
