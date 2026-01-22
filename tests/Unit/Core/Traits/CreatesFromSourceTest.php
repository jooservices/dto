<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core\Traits;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Traits\CreatesFromSource;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class CreatesFromSourceTest extends TestCase
{
    protected function tearDown(): void
    {
        TestClass::resetEngine();

        parent::tearDown();
    }

    public function test_from_array(): void
    {
        $data = ['name' => 'John', 'age' => 30];

        $result = TestClass::fromArray($data);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from_array_with_context(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $context = new Context;

        $result = TestClass::fromArray($data, $context);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from_json(): void
    {
        $json = json_encode(['name' => 'John', 'age' => 30], JSON_THROW_ON_ERROR);

        $result = TestClass::fromJson($json);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from_json_with_context(): void
    {
        $json = json_encode(['name' => 'John', 'age' => 30], JSON_THROW_ON_ERROR);
        $context = new Context;

        $result = TestClass::fromJson($json, $context);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from_object(): void
    {
        $obj = new stdClass;
        $obj->name = 'John';
        $obj->age = 30;

        $result = TestClass::fromObject($obj);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from_object_with_context(): void
    {
        $obj = new stdClass;
        $obj->name = 'John';
        $obj->age = 30;
        $context = new Context;

        $result = TestClass::fromObject($obj, $context);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from(): void
    {
        $data = ['name' => 'John', 'age' => 30];

        $result = TestClass::from($data);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_from_with_context(): void
    {
        $data = ['name' => 'John', 'age' => 30];
        $context = new Context;

        $result = TestClass::from($data, $context);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_set_engine(): void
    {
        $engine = new \JOOservices\Dto\Tests\Fixtures\SpyEngine;
        $engine->hydrateResult = new TestClass('Test', 25);

        TestClass::setEngine($engine);
        TestClass::fromArray(['name' => 'Test', 'age' => 25]);

        $this->assertSame(1, $engine->hydrateCalls);
    }

    public function test_reset_engine(): void
    {
        $customEngine = new \JOOservices\Dto\Tests\Fixtures\SpyEngine;
        TestClass::setEngine($customEngine);
        TestClass::resetEngine();

        $result = TestClass::fromArray(['name' => 'John', 'age' => 30]);

        $this->assertInstanceOf(TestClass::class, $result);
    }

    public function test_engine_is_singleton(): void
    {
        $result1 = TestClass::fromArray(['name' => 'John', 'age' => 30]);
        $result2 = TestClass::fromArray(['name' => 'Jane', 'age' => 25]);

        $this->assertInstanceOf(TestClass::class, $result1);
        $this->assertInstanceOf(TestClass::class, $result2);
    }

    public function test_engine_is_lazily_created(): void
    {
        TestClass::resetEngine();

        $result = TestClass::fromArray(['name' => 'John', 'age' => 30]);

        $this->assertInstanceOf(TestClass::class, $result);
    }
}

final class TestClass
{
    use CreatesFromSource;

    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {}
}
