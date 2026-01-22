<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes\Validation;

use JOOservices\Dto\Attributes\Validation\Valid;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class ValidTest extends TestCase
{
    public function test_constructor_with_defaults(): void
    {
        $attr = new Valid;

        $this->assertFalse($attr->eachItem);
    }

    public function test_constructor_with_each_item(): void
    {
        $attr = new Valid(eachItem: true);

        $this->assertTrue($attr->eachItem);
    }

    public function test_readonly_class(): void
    {
        $reflection = new ReflectionClass(Valid::class);
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_can_be_applied_to_property(): void
    {
        $testClass = new class
        {
            #[Valid]
            public object $nestedDto;

            #[Valid(eachItem: true)]
            public array $items;
        };

        $reflection = new ReflectionClass($testClass);

        $nestedProp = $reflection->getProperty('nestedDto');
        $nestedAttrs = $nestedProp->getAttributes(Valid::class);
        $this->assertCount(1, $nestedAttrs);
        $this->assertFalse($nestedAttrs[0]->newInstance()->eachItem);

        $itemsProp = $reflection->getProperty('items');
        $itemsAttrs = $itemsProp->getAttributes(Valid::class);
        $this->assertCount(1, $itemsAttrs);
        $this->assertTrue($itemsAttrs[0]->newInstance()->eachItem);
    }
}
