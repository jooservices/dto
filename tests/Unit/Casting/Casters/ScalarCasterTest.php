<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting\Casters;

use JOOservices\Dto\Casting\Casters\ScalarCaster;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;

final class ScalarCasterTest extends TestCase
{
    private ScalarCaster $caster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->caster = new ScalarCaster;
    }

    public function test_supports_int_type(): void
    {
        $property = $this->createProperty('int');

        $this->assertTrue($this->caster->supports($property, '123'));
        $this->assertTrue($this->caster->supports($property, 123));
        $this->assertTrue($this->caster->supports($property, 123.5));
        $this->assertTrue($this->caster->supports($property, true));
    }

    public function test_supports_string_type(): void
    {
        $property = $this->createProperty('string');

        $this->assertTrue($this->caster->supports($property, 'test'));
        $this->assertTrue($this->caster->supports($property, 123));
        $this->assertTrue($this->caster->supports($property, true));
        $this->assertTrue($this->caster->supports($property, null));
    }

    public function test_supports_bool_type(): void
    {
        $property = $this->createProperty('bool');

        $this->assertTrue($this->caster->supports($property, true));
        $this->assertTrue($this->caster->supports($property, 'true'));
        $this->assertTrue($this->caster->supports($property, 1));
    }

    public function test_supports_float_type(): void
    {
        $property = $this->createProperty('float');

        $this->assertTrue($this->caster->supports($property, 1.5));
        $this->assertTrue($this->caster->supports($property, '1.5'));
        $this->assertTrue($this->caster->supports($property, 1));
    }

    public function test_does_not_support_non_scalar_types(): void
    {
        $property = $this->createProperty('array');

        $this->assertFalse($this->caster->supports($property, []));
    }

    public function test_cast_to_int(): void
    {
        $property = $this->createProperty('int');

        $this->assertSame(123, $this->caster->cast($property, '123', null));
        $this->assertSame(123, $this->caster->cast($property, 123, null));
        $this->assertSame(123, $this->caster->cast($property, 123.7, null));
        $this->assertSame(1, $this->caster->cast($property, true, null));
        $this->assertSame(0, $this->caster->cast($property, false, null));
    }

    public function test_cast_to_float(): void
    {
        $property = $this->createProperty('float');

        $this->assertSame(123.5, $this->caster->cast($property, '123.5', null));
        $this->assertSame(123.0, $this->caster->cast($property, 123, null));
        $this->assertSame(123.5, $this->caster->cast($property, 123.5, null));
        $this->assertSame(1.0, $this->caster->cast($property, true, null));
    }

    public function test_cast_to_string(): void
    {
        $property = $this->createProperty('string');

        $this->assertSame('test', $this->caster->cast($property, 'test', null));
        $this->assertSame('123', $this->caster->cast($property, 123, null));
        $this->assertSame('123.5', $this->caster->cast($property, 123.5, null));
        $this->assertSame('1', $this->caster->cast($property, true, null));
        $this->assertSame('', $this->caster->cast($property, false, null));
        $this->assertSame('', $this->caster->cast($property, null, null));
    }

    public function test_cast_to_bool(): void
    {
        $property = $this->createProperty('bool');

        $this->assertTrue($this->caster->cast($property, true, null));
        $this->assertFalse($this->caster->cast($property, false, null));
        $this->assertTrue($this->caster->cast($property, 1, null));
        $this->assertFalse($this->caster->cast($property, 0, null));
        $this->assertTrue($this->caster->cast($property, 'true', null));
        $this->assertFalse($this->caster->cast($property, 'false', null));
        $this->assertTrue($this->caster->cast($property, 'yes', null));
        $this->assertFalse($this->caster->cast($property, 'no', null));
        $this->assertTrue($this->caster->cast($property, '1', null));
        $this->assertFalse($this->caster->cast($property, '0', null));
        $this->assertFalse($this->caster->cast($property, '', null));
    }

    public function test_cast_to_bool_throws_for_invalid_string(): void
    {
        $property = $this->createProperty('bool');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'invalid', null);
    }

    public function test_cast_to_int_throws_for_non_numeric_string(): void
    {
        $property = $this->createProperty('int');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'not-a-number', null);
    }

    public function test_cast_to_float_throws_for_non_numeric_string(): void
    {
        $property = $this->createProperty('float');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'not-a-number', null);
    }

    public function test_cast_to_string_throws_for_array(): void
    {
        $property = $this->createProperty('string');

        $this->expectException(CastException::class);
        $this->caster->cast($property, ['array'], null);
    }

    public function test_cast_to_bool_handles_null(): void
    {
        $property = $this->createProperty('bool');

        $this->assertFalse($this->caster->cast($property, null, null));
    }

    public function test_cast_to_bool_case_insensitive(): void
    {
        $property = $this->createProperty('bool');

        $this->assertTrue($this->caster->cast($property, 'TRUE', null));
        $this->assertTrue($this->caster->cast($property, 'True', null));
        $this->assertTrue($this->caster->cast($property, 'YES', null));
        $this->assertTrue($this->caster->cast($property, 'ON', null));
        $this->assertFalse($this->caster->cast($property, 'FALSE', null));
        $this->assertFalse($this->caster->cast($property, 'NO', null));
        $this->assertFalse($this->caster->cast($property, 'OFF', null));
    }

    public function test_permissive_mode_returns_null_on_invalid_int_cast(): void
    {
        $property = $this->createProperty('int');
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'not-a-number', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_returns_null_on_invalid_float_cast(): void
    {
        $property = $this->createProperty('float');
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'not-a-number', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_returns_null_on_invalid_bool_cast(): void
    {
        $property = $this->createProperty('bool');
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'invalid-bool', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_returns_null_on_invalid_string_cast(): void
    {
        $property = $this->createProperty('string');
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, ['array'], $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_allows_valid_casts(): void
    {
        $context = \JOOservices\Dto\Core\Context::permissive();

        $intProperty = $this->createProperty('int');
        $this->assertSame(123, $this->caster->cast($intProperty, '123', $context));

        $floatProperty = $this->createProperty('float');
        $this->assertSame(123.5, $this->caster->cast($floatProperty, '123.5', $context));

        $stringProperty = $this->createProperty('string');
        $this->assertSame('test', $this->caster->cast($stringProperty, 'test', $context));

        $boolProperty = $this->createProperty('bool');
        $this->assertTrue($this->caster->cast($boolProperty, 'true', $context));
    }

    public function test_permissive_mode_returns_null_for_unsupported_type(): void
    {
        $property = $this->createProperty('unknown');
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'value', $context);

        $this->assertNull($result);
    }

    public function test_strict_mode_still_throws_exceptions(): void
    {
        $property = $this->createProperty('int');
        $context = new \JOOservices\Dto\Core\Context(castMode: 'strict');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'not-a-number', $context);
    }

    public function test_loose_mode_still_throws_exceptions(): void
    {
        $property = $this->createProperty('int');
        $context = new \JOOservices\Dto\Core\Context(castMode: 'loose');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'not-a-number', $context);
    }

    private function createProperty(string $typeName): PropertyMeta
    {
        $type = new TypeDescriptor(
            name: $typeName,
            isBuiltin: true,
            isNullable: false,
            isArray: $typeName === 'array',
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
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
