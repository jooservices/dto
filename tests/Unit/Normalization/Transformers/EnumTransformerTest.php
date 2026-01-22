<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization\Transformers;

use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Tests\Fixtures\Priority;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;

final class EnumTransformerTest extends TestCase
{
    private EnumTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new EnumTransformer;
    }

    public function test_supports_backed_enum(): void
    {
        $property = $this->createProperty();

        $this->assertTrue($this->transformer->supports($property, Status::Active));
    }

    public function test_supports_unit_enum(): void
    {
        $property = $this->createProperty();

        $this->assertTrue($this->transformer->supports($property, Priority::High));
    }

    public function test_does_not_support_non_enum(): void
    {
        $property = $this->createProperty();

        $this->assertFalse($this->transformer->supports($property, 'string'));
        $this->assertFalse($this->transformer->supports($property, 123));
        $this->assertFalse($this->transformer->supports($property, null));
    }

    public function test_transform_backed_enum_returns_value(): void
    {
        $property = $this->createProperty();

        $this->assertSame('active', $this->transformer->transform($property, Status::Active, null));
        $this->assertSame('inactive', $this->transformer->transform($property, Status::Inactive, null));
        $this->assertSame('pending', $this->transformer->transform($property, Status::Pending, null));
    }

    public function test_transform_unit_enum_returns_name(): void
    {
        $property = $this->createProperty();

        $this->assertSame('Low', $this->transformer->transform($property, Priority::Low, null));
        $this->assertSame('Medium', $this->transformer->transform($property, Priority::Medium, null));
        $this->assertSame('High', $this->transformer->transform($property, Priority::High, null));
    }

    public function test_transform_returns_empty_string_for_non_enum(): void
    {
        $property = $this->createProperty();

        $result = $this->transformer->transform($property, 'not-an-enum', null);

        $this->assertSame('', $result);
    }

    private function createProperty(): PropertyMeta
    {
        return new PropertyMeta(
            name: $this->faker->word(),
            type: TypeDescriptor::mixed(),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );
    }
}
