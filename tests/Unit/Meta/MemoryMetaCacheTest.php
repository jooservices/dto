<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Tests\TestCase;

final class MemoryMetaCacheTest extends TestCase
{
    public function test_set_and_get(): void
    {
        $cache = new MemoryMetaCache;
        $className = 'App\\Dto\\'.$this->faker->word();
        $meta = $this->createClassMeta($className);

        $cache->set($className, $meta);

        $this->assertSame($meta, $cache->get($className));
    }

    public function test_get_returns_null_for_missing_key(): void
    {
        $cache = new MemoryMetaCache;

        $this->assertNull($cache->get('NonExistentClass'));
    }

    public function test_has(): void
    {
        $cache = new MemoryMetaCache;
        $className = 'App\\Dto\\'.$this->faker->word();
        $meta = $this->createClassMeta($className);

        $this->assertFalse($cache->has($className));

        $cache->set($className, $meta);

        $this->assertTrue($cache->has($className));
    }

    public function test_clear(): void
    {
        $cache = new MemoryMetaCache;
        $className1 = 'App\\Dto\\'.$this->faker->word();
        $className2 = 'App\\Dto\\'.$this->faker->word();

        $cache->set($className1, $this->createClassMeta($className1));
        $cache->set($className2, $this->createClassMeta($className2));

        $this->assertTrue($cache->has($className1));
        $this->assertTrue($cache->has($className2));

        $cache->clear();

        $this->assertFalse($cache->has($className1));
        $this->assertFalse($cache->has($className2));
    }

    public function test_get_count(): void
    {
        $cache = new MemoryMetaCache;

        $this->assertSame(0, $cache->getCount());

        $cache->set('Class1', $this->createClassMeta('Class1'));
        $cache->set('Class2', $this->createClassMeta('Class2'));

        $this->assertSame(2, $cache->getCount());
    }

    public function test_get_cached_classes(): void
    {
        $cache = new MemoryMetaCache;

        $this->assertSame([], $cache->getCachedClasses());

        $cache->set('ClassA', $this->createClassMeta('ClassA'));
        $cache->set('ClassB', $this->createClassMeta('ClassB'));

        $classes = $cache->getCachedClasses();

        $this->assertCount(2, $classes);
        $this->assertContains('ClassA', $classes);
        $this->assertContains('ClassB', $classes);
    }

    public function test_overwrite_existing_entry(): void
    {
        $cache = new MemoryMetaCache;
        $className = 'App\\Dto\\User';

        $meta1 = $this->createClassMeta($className);
        $meta2 = $this->createClassMeta($className);

        $cache->set($className, $meta1);
        $cache->set($className, $meta2);

        $this->assertSame($meta2, $cache->get($className));
        $this->assertSame(1, $cache->getCount());
    }

    private function createClassMeta(string $className): ClassMeta
    {
        return new ClassMeta(
            className: $className,
            isReadonly: true,
            properties: [],
            constructorParams: [],
            attributes: [],
        );
    }
}
