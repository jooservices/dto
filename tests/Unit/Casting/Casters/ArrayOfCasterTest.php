<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting\Casters;

use DateTimeImmutable;
use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Casting\Casters\ArrayOfCaster;
use JOOservices\Dto\Casting\Casters\DateTimeCaster;
use JOOservices\Dto\Casting\Casters\ScalarCaster;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;

final class ArrayOfCasterTest extends TestCase
{
    private ArrayOfCaster $caster;

    private CasterRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new CasterRegistry;
        $this->registry->register(new ScalarCaster, 10);
        $this->registry->register(new DateTimeCaster, 20);

        $this->caster = new ArrayOfCaster($this->registry);
    }

    public function test_supports_typed_array(): void
    {
        $property = $this->createTypedArrayProperty('string');

        $this->assertTrue($this->caster->supports($property, ['test']));
    }

    public function test_does_not_support_non_array(): void
    {
        $property = $this->createTypedArrayProperty('string');

        $this->assertFalse($this->caster->supports($property, 'not an array'));
    }

    public function test_does_not_support_non_typed_array(): void
    {
        $property = $this->createNonTypedArrayProperty();

        $this->assertFalse($this->caster->supports($property, ['test']));
    }

    public function test_cast_throws_exception_for_non_array(): void
    {
        $property = $this->createTypedArrayProperty('string');

        $this->expectException(CastException::class);
        $this->expectExceptionMessage('Cannot cast');

        $this->caster->cast($property, 'not an array', null);
    }

    public function test_cast_returns_array_with_null_item_type(): void
    {
        $property = new PropertyMeta(
            name: 'items',
            type: new TypeDescriptor(
                name: 'array',
                isBuiltin: true,
                isNullable: false,
                isArray: true,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            ),
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

        $input = ['a', 'b', 'c'];
        $result = $this->caster->cast($property, $input, null);

        $this->assertSame($input, $result);
    }

    public function test_cast_array_of_strings(): void
    {
        $property = $this->createTypedArrayProperty('string');
        $input = ['1', '2', '3'];

        $result = $this->caster->cast($property, $input, null);

        $this->assertSame($input, $result);
    }

    public function test_cast_array_of_integers(): void
    {
        $property = $this->createTypedArrayProperty('int');
        $input = [1, 2, 3];

        $result = $this->caster->cast($property, $input, null);

        $this->assertSame($input, $result);
    }

    public function test_cast_array_of_date_times(): void
    {
        $property = $this->createTypedArrayProperty(DateTimeImmutable::class, true);
        $input = [
            '2026-01-15T10:30:00+00:00',
            '2026-01-16T10:30:00+00:00',
        ];

        $result = $this->caster->cast($property, $input, null);

        $this->assertCount(2, $result);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[0]);
        $this->assertInstanceOf(DateTimeImmutable::class, $result[1]);
    }

    public function test_cast_preserves_array_keys(): void
    {
        $property = $this->createTypedArrayProperty('string');
        $input = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $result = $this->caster->cast($property, $input, null);

        $this->assertArrayHasKey('key1', $result);
        $this->assertArrayHasKey('key2', $result);
        $this->assertSame('value1', $result['key1']);
        $this->assertSame('value2', $result['key2']);
    }

    public function test_cast_with_context(): void
    {
        $property = $this->createTypedArrayProperty('string');
        $context = new Context;
        $input = ['test'];

        $result = $this->caster->cast($property, $input, $context);

        $this->assertSame($input, $result);
    }

    public function test_cast_skips_items_without_caster(): void
    {
        $property = $this->createTypedArrayProperty('CustomClass');
        $input = ['item1', 'item2'];

        $result = $this->caster->cast($property, $input, null);

        $this->assertSame($input, $result);
    }

    public function test_cast_handles_empty_array(): void
    {
        $property = $this->createTypedArrayProperty('string');
        $input = [];

        $result = $this->caster->cast($property, $input, null);

        $this->assertSame([], $result);
    }

    public function test_cast_handles_mixed_array_keys(): void
    {
        $property = $this->createTypedArrayProperty('string');
        $input = [
            0 => 'zero',
            'key' => 'value',
            2 => 'two',
        ];

        $result = $this->caster->cast($property, $input, null);

        $this->assertSame('zero', $result[0]);
        $this->assertSame('value', $result['key']);
        $this->assertSame('two', $result[2]);
    }

    private function createTypedArrayProperty(string $itemTypeName, bool $isDateTime = false): PropertyMeta
    {
        return new PropertyMeta(
            name: 'items',
            type: new TypeDescriptor(
                name: 'array',
                isBuiltin: true,
                isNullable: false,
                isArray: true,
                arrayItemType: new TypeDescriptor(
                    name: $itemTypeName,
                    isBuiltin: in_array($itemTypeName, ['string', 'int', 'float', 'bool'], true),
                    isNullable: false,
                    isArray: false,
                    arrayItemType: null,
                    isEnum: false,
                    enumClass: null,
                    isDto: false,
                    isDateTime: $isDateTime,
                ),
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            ),
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

    private function createNonTypedArrayProperty(): PropertyMeta
    {
        return new PropertyMeta(
            name: 'items',
            type: new TypeDescriptor(
                name: 'array',
                isBuiltin: true,
                isNullable: false,
                isArray: false,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            ),
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
