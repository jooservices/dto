<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class PropertyMetaTest extends TestCase
{
    public function test_get_source_key_with_map_from(): void
    {
        $mapFromKey = $this->faker->word().'_'.$this->faker->word();
        $propertyName = $this->faker->word();

        $property = new PropertyMeta(
            name: $propertyName,
            type: TypeDescriptor::mixed(),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: $mapFromKey,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );

        $this->assertSame($mapFromKey, $property->getSourceKey());
    }

    public function test_get_source_key_without_map_from(): void
    {
        $propertyName = $this->faker->word();

        $property = new PropertyMeta(
            name: $propertyName,
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

        $this->assertSame($propertyName, $property->getSourceKey());
    }

    public function test_requires_casting(): void
    {
        $propertyWithCaster = new PropertyMeta(
            name: $this->faker->word(),
            type: TypeDescriptor::mixed(),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: 'App\\Casters\\CustomCaster',
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );

        $propertyWithoutCaster = new PropertyMeta(
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

        $this->assertTrue($propertyWithCaster->requiresCasting());
        $this->assertFalse($propertyWithoutCaster->requiresCasting());
    }

    public function test_requires_transformation(): void
    {
        $propertyWithTransformer = new PropertyMeta(
            name: $this->faker->word(),
            type: TypeDescriptor::mixed(),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: 'App\\Transformers\\CustomTransformer',
            isHidden: false,
            validationRules: [],
            attributes: [],
        );

        $propertyWithoutTransformer = new PropertyMeta(
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

        $this->assertTrue($propertyWithTransformer->requiresTransformation());
        $this->assertFalse($propertyWithoutTransformer->requiresTransformation());
    }

    public function test_has_validation_rules(): void
    {
        $propertyWithRules = new PropertyMeta(
            name: $this->faker->word(),
            type: TypeDescriptor::mixed(),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [new stdClass],
            attributes: [],
        );

        $propertyWithoutRules = new PropertyMeta(
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

        $this->assertTrue($propertyWithRules->hasValidationRules());
        $this->assertFalse($propertyWithoutRules->hasValidationRules());
    }

    public function test_is_required_without_default_and_non_nullable(): void
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

        $this->assertTrue($property->isRequired());
    }

    public function test_is_required_returns_false_with_default(): void
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
            hasDefault: true,
            defaultValue: $this->faker->word(),
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );

        $this->assertFalse($property->isRequired());
    }

    public function test_is_required_returns_false_when_nullable(): void
    {
        $type = new TypeDescriptor(
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

        $this->assertFalse($property->isRequired());
    }

    public function test_can_be_null(): void
    {
        $nullableType = new TypeDescriptor(
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

        $nonNullableType = new TypeDescriptor(
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

        $nullableProperty = new PropertyMeta(
            name: $this->faker->word(),
            type: $nullableType,
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

        $nonNullableProperty = new PropertyMeta(
            name: $this->faker->word(),
            type: $nonNullableType,
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

        $this->assertTrue($nullableProperty->canBeNull());
        $this->assertFalse($nonNullableProperty->canBeNull());
    }

    public function test_get_attribute(): void
    {
        $mapFromAttribute = new MapFrom('test_key');
        $hiddenAttribute = new Hidden;

        $property = new PropertyMeta(
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
            attributes: [$mapFromAttribute, $hiddenAttribute],
        );

        $this->assertSame($mapFromAttribute, $property->getAttribute(MapFrom::class));
        $this->assertSame($hiddenAttribute, $property->getAttribute(Hidden::class));
        $this->assertNull($property->getAttribute(stdClass::class));
    }

    public function test_has_attribute(): void
    {
        $property = new PropertyMeta(
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
            attributes: [new Hidden],
        );

        $this->assertTrue($property->hasAttribute(Hidden::class));
        $this->assertFalse($property->hasAttribute(MapFrom::class));
    }
}
