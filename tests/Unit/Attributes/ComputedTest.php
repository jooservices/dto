<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes;

use JOOservices\Dto\Attributes\Computed;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ComputedTest extends TestCase
{
    public function test_constructor_with_defaults(): void
    {
        $computed = new Computed;

        $this->assertTrue($computed->cached);
        $this->assertTrue($computed->includeInSerialization);
    }

    public function test_constructor_with_custom_values(): void
    {
        $computed = new Computed(
            cached: false,
            includeInSerialization: false,
        );

        $this->assertFalse($computed->cached);
        $this->assertFalse($computed->includeInSerialization);
    }

    public function test_readonly_properties(): void
    {
        $reflection = new ReflectionClass(Computed::class);

        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_attribute_target(): void
    {
        $reflection = new ReflectionClass(Computed::class);
        $attributes = $reflection->getAttributes();

        $this->assertCount(1, $attributes);
        $this->assertSame('Attribute', $attributes[0]->getName());
    }

    public function test_can_be_applied_to_method(): void
    {
        $testClass = new class
        {
            #[Computed]
            public function test_method(): string
            {
                return 'test';
            }
        };

        $reflection = new ReflectionClass($testClass);
        $method = $reflection->getMethod('test_method');
        $attributes = $method->getAttributes(Computed::class);

        $this->assertCount(1, $attributes);

        $computed = $attributes[0]->newInstance();
        $this->assertInstanceOf(Computed::class, $computed);
    }
}
