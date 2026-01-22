<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use ArrayObject;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class ClassMetaTest extends TestCase
{
    public function test_has_property(): void
    {
        $propertyName = $this->faker->word();
        $property = $this->createProperty($propertyName);

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [$propertyName => $property],
            constructorParams: [$propertyName],
            attributes: [],
        );

        $this->assertTrue($meta->hasProperty($propertyName));
        $this->assertFalse($meta->hasProperty('nonexistent'));
    }

    public function test_get_property(): void
    {
        $propertyName = $this->faker->word();
        $property = $this->createProperty($propertyName);

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [$propertyName => $property],
            constructorParams: [],
            attributes: [],
        );

        $this->assertSame($property, $meta->getProperty($propertyName));
        $this->assertNull($meta->getProperty('nonexistent'));
    }

    public function test_get_required_properties(): void
    {
        $requiredProp = $this->createProperty('required', false, false);
        $optionalProp = $this->createProperty('optional', true, true);

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [
                'required' => $requiredProp,
                'optional' => $optionalProp,
            ],
            constructorParams: [],
            attributes: [],
        );

        $required = $meta->getRequiredProperties();

        $this->assertCount(1, $required);
        $this->assertArrayHasKey('required', $required);
    }

    public function test_get_optional_properties(): void
    {
        $requiredProp = $this->createProperty('required', false, false);
        $optionalProp = $this->createProperty('optional', true, true);

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [
                'required' => $requiredProp,
                'optional' => $optionalProp,
            ],
            constructorParams: [],
            attributes: [],
        );

        $optional = $meta->getOptionalProperties();

        $this->assertCount(1, $optional);
        $this->assertArrayHasKey('optional', $optional);
    }

    public function test_get_hidden_properties(): void
    {
        $visibleProp = $this->createProperty('visible', false, false, false);
        $hiddenProp = $this->createProperty('hidden', false, false, true);

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [
                'visible' => $visibleProp,
                'hidden' => $hiddenProp,
            ],
            constructorParams: [],
            attributes: [],
        );

        $hidden = $meta->getHiddenProperties();

        $this->assertCount(1, $hidden);
        $this->assertArrayHasKey('hidden', $hidden);
    }

    public function test_get_visible_properties(): void
    {
        $visibleProp = $this->createProperty('visible', false, false, false);
        $hiddenProp = $this->createProperty('hidden', false, false, true);

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [
                'visible' => $visibleProp,
                'hidden' => $hiddenProp,
            ],
            constructorParams: [],
            attributes: [],
        );

        $visible = $meta->getVisibleProperties();

        $this->assertCount(1, $visible);
        $this->assertArrayHasKey('visible', $visible);
    }

    public function test_get_property_count(): void
    {
        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [
                'prop1' => $this->createProperty('prop1'),
                'prop2' => $this->createProperty('prop2'),
                'prop3' => $this->createProperty('prop3'),
            ],
            constructorParams: [],
            attributes: [],
        );

        $this->assertSame(3, $meta->getPropertyCount());
    }

    public function test_get_property_names(): void
    {
        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [
                'alpha' => $this->createProperty('alpha'),
                'beta' => $this->createProperty('beta'),
            ],
            constructorParams: [],
            attributes: [],
        );

        $this->assertSame(['alpha', 'beta'], $meta->getPropertyNames());
    }

    public function test_get_attribute(): void
    {
        $attribute = new stdClass;
        $attribute->value = $this->faker->word();

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [],
            constructorParams: [],
            attributes: [$attribute],
        );

        $this->assertSame($attribute, $meta->getAttribute(stdClass::class));
        $this->assertNull($meta->getAttribute(ArrayObject::class));
    }

    public function test_has_attribute(): void
    {
        $attribute = new stdClass;

        $meta = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [],
            constructorParams: [],
            attributes: [$attribute],
        );

        $this->assertTrue($meta->hasAttribute(stdClass::class));
        $this->assertFalse($meta->hasAttribute(ArrayObject::class));
    }

    public function test_is_constructor_based(): void
    {
        $constructorBased = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [],
            constructorParams: ['name', 'age'],
            attributes: [],
        );

        $notConstructorBased = new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: [],
            constructorParams: [],
            attributes: [],
        );

        $this->assertTrue($constructorBased->isConstructorBased());
        $this->assertFalse($notConstructorBased->isConstructorBased());
    }

    private function createProperty(
        string $name,
        bool $hasDefault = false,
        bool $isNullable = true,
        bool $isHidden = false,
    ): PropertyMeta {
        $type = new TypeDescriptor(
            name: 'string',
            isBuiltin: true,
            isNullable: $isNullable,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        return new PropertyMeta(
            name: $name,
            type: $type,
            isReadonly: true,
            hasDefault: $hasDefault,
            defaultValue: $hasDefault ? '' : null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: $isHidden,
            validationRules: [],
            attributes: [],
        );
    }
}
