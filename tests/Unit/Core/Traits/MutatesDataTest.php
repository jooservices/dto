<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core\Traits;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Traits\MutatesData;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Tests\TestCase;

final class MutatesDataTest extends TestCase
{
    protected function tearDown(): void
    {
        MutableTestClass::resetMutationEngine();

        parent::tearDown();
    }

    public function test_update(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->update(['name' => 'Jane', 'age' => 25]);

        $this->assertSame('Jane', $obj->name);
        $this->assertSame(25, $obj->age);
    }

    public function test_update_single_property(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->update(['name' => 'Jane']);

        $this->assertSame('Jane', $obj->name);
        $this->assertSame(30, $obj->age);
    }

    public function test_update_throws_exception_for_non_existent_property(): void
    {
        $obj = new MutableTestClass('John', 30);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage("Property 'nonexistent' does not exist");

        $obj->update(['nonexistent' => 'value']);
    }

    public function test_update_throws_exception_for_non_public_property(): void
    {
        $obj = new MutableTestClassWithPrivate('John', 30, 'secret');

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage("Property 'privateProperty' is not accessible");

        $obj->update(['privateProperty' => 'new value']);
    }

    public function test_set(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->set('name', 'Jane');

        $this->assertSame('Jane', $obj->name);
    }

    public function test_set_calls_update(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->set('age', 25);

        $this->assertSame(25, $obj->age);
    }

    public function test_update_with_empty_array(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->update([]);

        $this->assertSame('John', $obj->name);
        $this->assertSame(30, $obj->age);
    }

    public function test_update_preserves_unchanged_properties(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->update(['name' => 'Jane']);

        $this->assertSame('Jane', $obj->name);
        $this->assertSame(30, $obj->age);
    }

    public function test_set_mutation_engine(): void
    {
        $engine = new \JOOservices\Dto\Tests\Fixtures\SpyEngine;

        MutableTestClass::setMutationEngine($engine);
        MutableTestClass::resetMutationEngine();

        $this->assertTrue(true);
    }

    public function test_reset_mutation_engine(): void
    {
        $engine = new \JOOservices\Dto\Tests\Fixtures\SpyEngine;

        MutableTestClass::setMutationEngine($engine);
        MutableTestClass::resetMutationEngine();

        $obj = new MutableTestClass('John', 30);
        $obj->update(['name' => 'Jane']);

        $this->assertSame('Jane', $obj->name);
    }

    public function test_update_with_context(): void
    {
        $obj = new MutableTestClass('John', 30);
        $context = new Context;

        $obj->update(['name' => 'Jane'], $context);

        $this->assertSame('Jane', $obj->name);
    }

    public function test_set_throws_exception_for_non_existent_property(): void
    {
        $obj = new MutableTestClass('John', 30);

        $this->expectException(HydrationException::class);

        $obj->set('nonexistent', 'value');
    }

    public function test_update_multiple_properties(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->update([
            'name' => 'Jane',
            'age' => 25,
        ]);

        $this->assertSame('Jane', $obj->name);
        $this->assertSame(25, $obj->age);
    }

    public function test_update_with_null_value(): void
    {
        $obj = new MutableTestClass('John', 30);

        $obj->update(['name' => null]);

        $this->assertNull($obj->name);
    }
}

final class MutableTestClass
{
    use MutatesData;

    public function __construct(
        public ?string $name,
        public int $age,
    ) {}
}

final class MutableTestClassWithPrivate
{
    use MutatesData;

    public function __construct(
        public string $name,
        public int $age,
        private string $privateProperty,
    ) {}
}
