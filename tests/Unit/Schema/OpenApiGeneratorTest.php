<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Schema;

use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Schema\OpenApiGenerator;
use PHPUnit\Framework\TestCase;

final class OpenApiGeneratorTest extends TestCase
{
    public function test_get_format(): void
    {
        $metaFactory = self::createStub(MetaFactoryInterface::class);
        $generator = new OpenApiGenerator($metaFactory);

        $this->assertSame('openapi-3.0', $generator->getFormat());
    }

    public function test_generate_basic_schema(): void
    {
        $properties = [
            new PropertyMeta(
                name: 'name',
                type: new TypeDescriptor(
                    name: 'string',
                    isBuiltin: true,
                    isNullable: false,
                    isArray: false,
                    arrayItemType: null,
                    isEnum: false,
                    enumClass: null,
                    isDto: false,
                    isDateTime: false,
                ),
                isReadonly: false,
                hasDefault: false,
                defaultValue: null,
                mapFrom: null,
                casterClass: null,
                transformerClass: null,
                isHidden: false,
                validationRules: [],
                attributes: [],
            ),
            new PropertyMeta(
                name: 'count',
                type: new TypeDescriptor(
                    name: 'int',
                    isBuiltin: true,
                    isNullable: false,
                    isArray: false,
                    arrayItemType: null,
                    isEnum: false,
                    enumClass: null,
                    isDto: false,
                    isDateTime: false,
                ),
                isReadonly: false,
                hasDefault: false,
                defaultValue: null,
                mapFrom: null,
                casterClass: null,
                transformerClass: null,
                isHidden: false,
                validationRules: [],
                attributes: [],
            ),
        ];

        $classMeta = $this->createClassMeta($properties);

        $metaFactory = $this->createMock(MetaFactoryInterface::class);
        $metaFactory->expects($this->once())
            ->method('create')
            ->with('TestDto')
            ->willReturn($classMeta);

        $generator = new OpenApiGenerator($metaFactory);
        $schema = $generator->generate('TestDto');

        $this->assertSame('object', $schema['type']);
        $this->assertArrayHasKey('name', $schema['properties']);
        $this->assertArrayHasKey('count', $schema['properties']);
        $this->assertSame(['name', 'count'], $schema['required']);
        $this->assertSame('int64', $schema['properties']['count']['format']);
    }

    public function test_generate_skips_hidden_properties(): void
    {
        $properties = [
            new PropertyMeta(
                name: 'visible',
                type: new TypeDescriptor(
                    name: 'string',
                    isBuiltin: true,
                    isNullable: false,
                    isArray: false,
                    arrayItemType: null,
                    isEnum: false,
                    enumClass: null,
                    isDto: false,
                    isDateTime: false,
                ),
                isReadonly: false,
                hasDefault: false,
                defaultValue: null,
                mapFrom: null,
                casterClass: null,
                transformerClass: null,
                isHidden: false,
                validationRules: [],
                attributes: [],
            ),
            new PropertyMeta(
                name: 'secret',
                type: new TypeDescriptor(
                    name: 'string',
                    isBuiltin: true,
                    isNullable: false,
                    isArray: false,
                    arrayItemType: null,
                    isEnum: false,
                    enumClass: null,
                    isDto: false,
                    isDateTime: false,
                ),
                isReadonly: false,
                hasDefault: false,
                defaultValue: null,
                mapFrom: null,
                casterClass: null,
                transformerClass: null,
                isHidden: true,
                validationRules: [],
                attributes: [],
            ),
        ];

        $classMeta = $this->createClassMeta($properties);

        $metaFactory = self::createStub(MetaFactoryInterface::class);
        $metaFactory->method('create')->willReturn($classMeta);

        $generator = new OpenApiGenerator($metaFactory);
        $schema = $generator->generate('TestDto');

        $this->assertArrayHasKey('visible', $schema['properties']);
        $this->assertArrayNotHasKey('secret', $schema['properties']);
    }

    public function test_generate_handles_nullable_types(): void
    {
        $properties = [
            new PropertyMeta(
                name: 'optional',
                type: new TypeDescriptor(
                    name: 'string',
                    isBuiltin: true,
                    isNullable: true,
                    isArray: false,
                    arrayItemType: null,
                    isEnum: false,
                    enumClass: null,
                    isDto: false,
                    isDateTime: false,
                ),
                isReadonly: false,
                hasDefault: true,
                defaultValue: null,
                mapFrom: null,
                casterClass: null,
                transformerClass: null,
                isHidden: false,
                validationRules: [],
                attributes: [],
            ),
        ];

        $classMeta = $this->createClassMeta($properties);

        $metaFactory = self::createStub(MetaFactoryInterface::class);
        $metaFactory->method('create')->willReturn($classMeta);

        $generator = new OpenApiGenerator($metaFactory);
        $schema = $generator->generate('TestDto');

        $this->assertTrue($schema['properties']['optional']['nullable']);
    }

    /**
     * @param  array<PropertyMeta>  $properties
     */
    private function createClassMeta(array $properties): ClassMeta
    {
        // Convert indexed array to associative array keyed by property name
        $indexedProps = [];
        foreach ($properties as $prop) {
            $indexedProps[$prop->name] = $prop;
        }

        return new ClassMeta(
            className: 'TestDto',
            isReadonly: false,
            properties: $indexedProps,
            constructorParams: [],
            attributes: [],
        );
    }
}
