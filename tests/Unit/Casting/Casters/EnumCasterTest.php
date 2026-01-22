<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting\Casters;

use JOOservices\Dto\Casting\Casters\EnumCaster;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\Priority;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;

final class EnumCasterTest extends TestCase
{
    private EnumCaster $caster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->caster = new EnumCaster;
    }

    public function test_supports_backed_enum_with_string(): void
    {
        $property = $this->createEnumProperty(Status::class, true);

        $this->assertTrue($this->caster->supports($property, 'active'));
        $this->assertTrue($this->caster->supports($property, Status::Active));
    }

    public function test_supports_unit_enum_with_string(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);

        $this->assertTrue($this->caster->supports($property, 'Low'));
        $this->assertTrue($this->caster->supports($property, Priority::Low));
    }

    public function test_does_not_support_non_enum_type(): void
    {
        $type = new TypeDescriptor(
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

        $property = new PropertyMeta(
            name: $this->faker->word(),
            type: $type,
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

        $this->assertFalse($this->caster->supports($property, 'test'));
    }

    public function test_cast_backed_enum_from_value(): void
    {
        $property = $this->createEnumProperty(Status::class, true);

        $result = $this->caster->cast($property, 'active', null);

        $this->assertSame(Status::Active, $result);
    }

    public function test_cast_backed_enum_with_all_values(): void
    {
        $property = $this->createEnumProperty(Status::class, true);

        $this->assertSame(Status::Active, $this->caster->cast($property, 'active', null));
        $this->assertSame(Status::Inactive, $this->caster->cast($property, 'inactive', null));
        $this->assertSame(Status::Pending, $this->caster->cast($property, 'pending', null));
    }

    public function test_cast_backed_enum_from_enum_instance(): void
    {
        $property = $this->createEnumProperty(Status::class, true);

        $result = $this->caster->cast($property, Status::Active, null);

        $this->assertSame(Status::Active, $result);
    }

    public function test_cast_unit_enum_from_name(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);

        $this->assertSame(Priority::Low, $this->caster->cast($property, 'Low', null));
        $this->assertSame(Priority::Medium, $this->caster->cast($property, 'Medium', null));
        $this->assertSame(Priority::High, $this->caster->cast($property, 'High', null));
    }

    public function test_cast_unit_enum_from_enum_instance(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);

        $result = $this->caster->cast($property, Priority::High, null);

        $this->assertSame(Priority::High, $result);
    }

    public function test_cast_backed_enum_throws_for_invalid_value(): void
    {
        $property = $this->createEnumProperty(Status::class, true);

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'invalid_status', null);
    }

    public function test_cast_unit_enum_throws_for_invalid_name(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'InvalidPriority', null);
    }

    public function test_cast_unit_enum_throws_for_non_string_value(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);

        $this->expectException(CastException::class);
        $this->caster->cast($property, 123, null);
    }

    public function test_cast_throws_when_enum_class_is_null(): void
    {
        $type = new TypeDescriptor(
            name: 'enum',
            isBuiltin: false,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: true,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $property = new PropertyMeta(
            name: $this->faker->word(),
            type: $type,
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

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'value', null);
    }

    public function test_permissive_mode_returns_null_on_invalid_backed_enum_value(): void
    {
        $property = $this->createEnumProperty(Status::class, true);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'invalid_status', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_returns_null_on_invalid_unit_enum_name(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'InvalidPriority', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_returns_null_when_enum_class_is_null(): void
    {
        $type = new TypeDescriptor(
            name: 'enum',
            isBuiltin: false,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: true,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $property = new PropertyMeta(
            name: $this->faker->word(),
            type: $type,
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

        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'value', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_allows_valid_backed_enum_casts(): void
    {
        $property = $this->createEnumProperty(Status::class, true);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'active', $context);

        $this->assertSame(Status::Active, $result);
    }

    public function test_permissive_mode_allows_valid_unit_enum_casts(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'High', $context);

        $this->assertSame(Priority::High, $result);
    }

    public function test_permissive_mode_returns_null_on_invalid_type_for_unit_enum(): void
    {
        $property = $this->createEnumProperty(Priority::class, false);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 123, $context);

        $this->assertNull($result);
    }

    public function test_strict_mode_still_throws_on_invalid_enum(): void
    {
        $property = $this->createEnumProperty(Status::class, true);
        $context = new \JOOservices\Dto\Core\Context(castMode: 'strict');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'invalid_status', $context);
    }

    private function createEnumProperty(string $enumClass, bool $isBacked): PropertyMeta
    {
        $type = new TypeDescriptor(
            name: $enumClass,
            isBuiltin: false,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: true,
            enumClass: $enumClass,
            isDto: false,
            isDateTime: false,
        );

        return new PropertyMeta(
            name: $this->faker->word(),
            type: $type,
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
