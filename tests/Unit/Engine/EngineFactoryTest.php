<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Engine;

use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Engine\EngineFactory;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Tests\TestCase;

final class EngineFactoryTest extends TestCase
{
    public function test_create_returns_engine(): void
    {
        $factory = new EngineFactory;

        $engine = $factory->create();

        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function test_with_meta_cache(): void
    {
        $factory = new EngineFactory;
        $cache = new MemoryMetaCache;

        $newFactory = $factory->withMetaCache($cache);

        $this->assertNotSame($factory, $newFactory);

        $engine = $newFactory->create();
        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function test_with_caster_registry(): void
    {
        $factory = new EngineFactory;
        $registry = new CasterRegistry;

        $newFactory = $factory->withCasterRegistry($registry);

        $this->assertNotSame($factory, $newFactory);

        $engine = $newFactory->create();
        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function test_with_transformer_registry(): void
    {
        $factory = new EngineFactory;
        $registry = new TransformerRegistry;

        $newFactory = $factory->withTransformerRegistry($registry);

        $this->assertNotSame($factory, $newFactory);

        $engine = $newFactory->create();
        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function test_chained_configuration(): void
    {
        $engine = new EngineFactory()
            ->withMetaCache(new MemoryMetaCache)
            ->withCasterRegistry(new CasterRegistry)
            ->withTransformerRegistry(new TransformerRegistry)
            ->create();

        $this->assertInstanceOf(Engine::class, $engine);
    }

    public function test_multiple_engines_can_be_created(): void
    {
        $factory = new EngineFactory;

        $engine1 = $factory->create();
        $engine2 = $factory->create();

        $this->assertInstanceOf(Engine::class, $engine1);
        $this->assertInstanceOf(Engine::class, $engine2);
        $this->assertNotSame($engine1, $engine2);
    }

    public function test_factory_is_immutable(): void
    {
        $factory = new EngineFactory;
        $cache = new MemoryMetaCache;

        $newFactory = $factory->withMetaCache($cache);

        $this->assertNotSame($factory, $newFactory);
    }
}
