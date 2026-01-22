<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes;

use JOOservices\Dto\Attributes\Deprecated;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DeprecatedTest extends TestCase
{
    public function test_constructor_with_defaults(): void
    {
        $attr = new Deprecated;

        $this->assertSame('This property is deprecated', $attr->message);
        $this->assertNull($attr->since);
        $this->assertNull($attr->useInstead);
    }

    public function test_constructor_with_all_parameters(): void
    {
        $attr = new Deprecated(
            message: 'Use new property',
            since: '2.0',
            useInstead: 'newProperty',
        );

        $this->assertSame('Use new property', $attr->message);
        $this->assertSame('2.0', $attr->since);
        $this->assertSame('newProperty', $attr->useInstead);
    }

    public function test_get_full_message_with_minimal_info(): void
    {
        $attr = new Deprecated(message: 'Deprecated property');

        $this->assertSame('Deprecated property', $attr->getFullMessage());
    }

    public function test_get_full_message_with_since(): void
    {
        $attr = new Deprecated(
            message: 'Old property',
            since: '1.5',
        );

        $this->assertSame('Old property (since 1.5)', $attr->getFullMessage());
    }

    public function test_get_full_message_with_use_instead(): void
    {
        $attr = new Deprecated(
            message: 'Old property',
            useInstead: 'modernProperty',
        );

        $this->assertSame("Old property. Use 'modernProperty' instead", $attr->getFullMessage());
    }

    public function test_get_full_message_with_all_info(): void
    {
        $attr = new Deprecated(
            message: 'Legacy field',
            since: '2.0',
            useInstead: 'newField',
        );

        $this->assertSame(
            "Legacy field (since 2.0). Use 'newField' instead",
            $attr->getFullMessage(),
        );
    }

    public function test_readonly_class(): void
    {
        $reflection = new ReflectionClass(Deprecated::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_can_be_applied_to_property(): void
    {
        $testClass = new class
        {
            #[Deprecated('Use newName', since: '1.0', useInstead: 'newName')]
            public string $oldName = 'test';

            public string $newName = 'test';
        };

        $reflection = new ReflectionClass($testClass);
        $property = $reflection->getProperty('oldName');
        $attributes = $property->getAttributes(Deprecated::class);

        $this->assertCount(1, $attributes);

        $deprecated = $attributes[0]->newInstance();
        $this->assertInstanceOf(Deprecated::class, $deprecated);
        $this->assertSame('Use newName', $deprecated->message);
    }
}
