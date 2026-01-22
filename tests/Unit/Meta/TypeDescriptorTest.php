<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use DateTimeImmutable;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\Priority;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;

final class TypeDescriptorTest extends TestCase
{
    public function test_mixed_type(): void
    {
        $descriptor = TypeDescriptor::mixed();

        $this->assertSame('mixed', $descriptor->name);
        $this->assertTrue($descriptor->isBuiltin);
        $this->assertTrue($descriptor->isNullable);
        $this->assertFalse($descriptor->isArray);
        $this->assertNull($descriptor->arrayItemType);
        $this->assertFalse($descriptor->isEnum);
        $this->assertFalse($descriptor->isDto);
    }

    public function test_for_class_with_dto(): void
    {
        $descriptor = TypeDescriptor::forClass(SimpleDto::class);

        $this->assertSame(SimpleDto::class, $descriptor->name);
        $this->assertFalse($descriptor->isBuiltin);
        $this->assertTrue($descriptor->isDto);
        $this->assertFalse($descriptor->isEnum);
    }

    public function test_for_class_with_backed_enum(): void
    {
        $descriptor = TypeDescriptor::forClass(Status::class);

        $this->assertSame(Status::class, $descriptor->name);
        $this->assertTrue($descriptor->isEnum);
        $this->assertSame(Status::class, $descriptor->enumClass);
        $this->assertTrue($descriptor->isBackedEnum());
        $this->assertFalse($descriptor->isUnitEnum());
    }

    public function test_for_class_with_unit_enum(): void
    {
        $descriptor = TypeDescriptor::forClass(Priority::class);

        $this->assertSame(Priority::class, $descriptor->name);
        $this->assertTrue($descriptor->isEnum);
        $this->assertSame(Priority::class, $descriptor->enumClass);
        $this->assertFalse($descriptor->isBackedEnum());
        $this->assertTrue($descriptor->isUnitEnum());
    }

    public function test_for_class_with_date_time(): void
    {
        $descriptor = TypeDescriptor::forClass(DateTimeImmutable::class);

        $this->assertTrue($descriptor->isDateTime);
        $this->assertFalse($descriptor->isEnum);
        $this->assertFalse($descriptor->isDto);
    }

    public function test_for_class_with_non_existent_class(): void
    {
        $descriptor = TypeDescriptor::forClass('NonExistentClass');

        $this->assertSame('mixed', $descriptor->name);
        $this->assertTrue($descriptor->isBuiltin);
    }

    public function test_with_array_item_type(): void
    {
        $descriptor = TypeDescriptor::mixed();
        $itemType = TypeDescriptor::forClass(SimpleDto::class);

        $arrayDescriptor = $descriptor->withArrayItemType($itemType);

        $this->assertTrue($arrayDescriptor->isArray);
        $this->assertSame($itemType, $arrayDescriptor->arrayItemType);
        $this->assertTrue($arrayDescriptor->isTypedArray());
    }

    public function test_with_nullable(): void
    {
        $descriptor = TypeDescriptor::forClass(SimpleDto::class);

        $this->assertFalse($descriptor->isNullable);

        $nullableDescriptor = $descriptor->withNullable(true);

        $this->assertTrue($nullableDescriptor->isNullable);
        $this->assertFalse($descriptor->isNullable);
    }

    public function test_is_scalar_for_string_type(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'string',
            isBuiltin: true,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertTrue($descriptor->isScalar());
    }

    public function test_is_scalar_for_int_type(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'int',
            isBuiltin: true,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertTrue($descriptor->isScalar());
    }

    public function test_is_scalar_for_float_type(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'float',
            isBuiltin: true,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertTrue($descriptor->isScalar());
    }

    public function test_is_scalar_for_bool_type(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'bool',
            isBuiltin: true,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertTrue($descriptor->isScalar());
    }

    public function test_is_scalar_returns_false_for_array(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'array',
            isBuiltin: true,
            isNullable: false,
            isArray: true,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertFalse($descriptor->isScalar());
    }

    public function test_accepts_null_for_nullable_type(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'string',
            isBuiltin: true,
            isNullable: true,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertTrue($descriptor->acceptsNull());
    }

    public function test_accepts_null_for_mixed_type(): void
    {
        $descriptor = TypeDescriptor::mixed();

        $this->assertTrue($descriptor->acceptsNull());
    }

    public function test_accepts_null_returns_false_for_non_nullable(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'string',
            isBuiltin: true,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertFalse($descriptor->acceptsNull());
    }

    public function test_is_typed_array_returns_false_for_untyped_array(): void
    {
        $descriptor = new TypeDescriptor(
            name: 'array',
            isBuiltin: true,
            isNullable: false,
            isArray: true,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $this->assertFalse($descriptor->isTypedArray());
    }
}
