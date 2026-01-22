<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use JOOservices\Dto\Normalization\ComputedCache;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ComputedCacheTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        ComputedCache::clearAll();
    }

    protected function tearDown(): void
    {
        ComputedCache::clearAll();
        parent::tearDown();
    }

    public function test_set_and_get(): void
    {
        $dto = new stdClass;
        $value = 'test value';

        ComputedCache::set($dto, 'property', $value);

        $this->assertSame($value, ComputedCache::get($dto, 'property'));
    }

    public function test_get_returns_null_for_non_existent_property(): void
    {
        $dto = new stdClass;

        $this->assertNull(ComputedCache::get($dto, 'nonexistent'));
    }

    public function test_get_returns_null_for_non_existent_object(): void
    {
        $dto1 = new stdClass;
        $dto2 = new stdClass;

        ComputedCache::set($dto1, 'property', 'value');

        $this->assertNull(ComputedCache::get($dto2, 'property'));
    }

    public function test_has(): void
    {
        $dto = new stdClass;

        $this->assertFalse(ComputedCache::has($dto, 'property'));

        ComputedCache::set($dto, 'property', 'value');

        $this->assertTrue(ComputedCache::has($dto, 'property'));
    }

    public function test_clear(): void
    {
        $dto = new stdClass;

        ComputedCache::set($dto, 'property1', 'value1');
        ComputedCache::set($dto, 'property2', 'value2');

        ComputedCache::clear($dto);

        $this->assertFalse(ComputedCache::has($dto, 'property1'));
        $this->assertFalse(ComputedCache::has($dto, 'property2'));
    }

    public function test_clear_all(): void
    {
        $dto1 = new stdClass;
        $dto2 = new stdClass;

        ComputedCache::set($dto1, 'property', 'value1');
        ComputedCache::set($dto2, 'property', 'value2');

        ComputedCache::clearAll();

        $this->assertFalse(ComputedCache::has($dto1, 'property'));
        $this->assertFalse(ComputedCache::has($dto2, 'property'));
    }

    public function test_weak_map_behavior(): void
    {
        $dto = new stdClass;
        ComputedCache::set($dto, 'property', 'value');

        $this->assertTrue(ComputedCache::has($dto, 'property'));

        // Unset the object - WeakMap should automatically remove it
        unset($dto);

        // Create new object - should not have old data
        $newDto = new stdClass;
        $this->assertFalse(ComputedCache::has($newDto, 'property'));
    }

    public function test_multiple_properties(): void
    {
        $dto = new stdClass;

        ComputedCache::set($dto, 'prop1', 'value1');
        ComputedCache::set($dto, 'prop2', 'value2');
        ComputedCache::set($dto, 'prop3', 'value3');

        $this->assertSame('value1', ComputedCache::get($dto, 'prop1'));
        $this->assertSame('value2', ComputedCache::get($dto, 'prop2'));
        $this->assertSame('value3', ComputedCache::get($dto, 'prop3'));
    }

    public function test_overwrite_value(): void
    {
        $dto = new stdClass;

        ComputedCache::set($dto, 'property', 'original');
        ComputedCache::set($dto, 'property', 'updated');

        $this->assertSame('updated', ComputedCache::get($dto, 'property'));
    }

    public function test_store_different_types(): void
    {
        $dto = new stdClass;

        ComputedCache::set($dto, 'string', 'text');
        ComputedCache::set($dto, 'int', 42);
        ComputedCache::set($dto, 'float', 3.14);
        ComputedCache::set($dto, 'bool', true);
        ComputedCache::set($dto, 'array', ['a', 'b']);
        ComputedCache::set($dto, 'null', null);

        $this->assertSame('text', ComputedCache::get($dto, 'string'));
        $this->assertSame(42, ComputedCache::get($dto, 'int'));
        $this->assertSame(3.14, ComputedCache::get($dto, 'float'));
        $this->assertTrue(ComputedCache::get($dto, 'bool'));
        $this->assertSame(['a', 'b'], ComputedCache::get($dto, 'array'));
        $this->assertNull(ComputedCache::get($dto, 'null'));
        $this->assertTrue(ComputedCache::has($dto, 'null')); // Null value is stored
    }
}
