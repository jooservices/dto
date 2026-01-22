<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes;

use JOOservices\Dto\Attributes\StrictType;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class StrictTypeTest extends TestCase
{
    public function test_constructor_with_defaults(): void
    {
        $attr = new StrictType;

        $this->assertSame('Type mismatch', $attr->message);
    }

    public function test_constructor_with_custom_message(): void
    {
        $attr = new StrictType(message: 'Custom error message');

        $this->assertSame('Custom error message', $attr->message);
    }

    public function test_readonly_class(): void
    {
        $reflection = new ReflectionClass(StrictType::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_can_be_applied_to_property(): void
    {
        $testClass = new class
        {
            #[StrictType]
            public int $strictProperty;
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('strictProperty');
        $attributes = $property->getAttributes(StrictType::class);

        $this->assertCount(1, $attributes);

        $strictType = $attributes[0]->newInstance();
        $this->assertInstanceOf(StrictType::class, $strictType);
    }
}
