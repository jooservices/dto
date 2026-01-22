<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Schema;

use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Schema\JsonSchemaGenerator;
use PHPUnit\Framework\TestCase;

final class JsonSchemaGeneratorTest extends TestCase
{
    public function test_get_format(): void
    {
        $metaFactory = self::createStub(MetaFactoryInterface::class);
        $generator = new JsonSchemaGenerator($metaFactory);

        $this->assertSame('json-schema', $generator->getFormat());
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
                name: 'age',
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

        $generator = new JsonSchemaGenerator($metaFactory);
        $schema = $generator->generate('TestDto');

        $this->assertSame('https://json-schema.org/draft/2020-12/schema', $schema['$schema']);
        $this->assertSame('object', $schema['type']);
        $this->assertArrayHasKey('name', $schema['properties']);
        $this->assertArrayHasKey('age', $schema['properties']);
        $this->assertSame(['name', 'age'], $schema['required']);
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
                name: 'hidden',
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

        $generator = new JsonSchemaGenerator($metaFactory);
        $schema = $generator->generate('TestDto');

        $this->assertArrayHasKey('visible', $schema['properties']);
        $this->assertArrayNotHasKey('hidden', $schema['properties']);
    }

    public function test_generate_handles_nullable_types(): void
    {
        $properties = [
            new PropertyMeta(
                name: 'nullable',
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

        $generator = new JsonSchemaGenerator($metaFactory);
        $schema = $generator->generate('TestDto');

        $this->assertArrayHasKey('anyOf', $schema['properties']['nullable']);
        $this->assertCount(2, $schema['properties']['nullable']['anyOf']);
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
