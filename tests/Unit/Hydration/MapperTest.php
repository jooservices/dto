<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Hydration\Naming\CamelCaseStrategy;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;

final class MapperTest extends TestCase
{
    /**
     * @param  list<object>  $attributes
     */
    private function property(string $name, array $attributes = []): PropertyMeta
    {
        return PropertyMetaBuilder::make(
            name: $name,
            type: TypeDescriptor::builtin('string'),
            attributes: $attributes,
        );
    }

    /**
     * @param  list<PropertyMeta>  $properties
     */
    private function meta(array $properties): ClassMeta
    {
        return new ClassMeta(
            className: UserDto::class,
            properties: $properties,
            ctorParams: array_map(static fn(PropertyMeta $property): string => $property->name, $properties),
            hasConstructor: true,
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testMapFromKeyUsedWhenOnlyMapFromSourcePresent(): void
    {
        $meta = $this->meta([$this->property('fullName', [new MapFrom('full_name')])]);
        $ctx = (new Context(false, false))->withNamingStrategy(new CamelCaseStrategy());

        $mapped = (new Mapper())->map(['full_name' => 'from-mapfrom'], $meta, $ctx);

        self::assertSame('from-mapfrom', $mapped['fullName']);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testConflictingSourceKeysAreRejected(): void
    {
        $meta = $this->meta([$this->property('fullName', [new MapFrom('full_name')])]);
        $ctx = (new Context(false, false))->withNamingStrategy(new CamelCaseStrategy());

        $this->expectException(MappingException::class);
        (new Mapper())->map([
            'full_name' => 'from-mapfrom',
            'fullName' => 'from-property-name',
        ], $meta, $ctx);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testConflictingNamingStrategyKeysAreRejected(): void
    {
        $meta = $this->meta([$this->property('firstName')]);
        $ctx = (new Context(false, false))->withNamingStrategy(new CamelCaseStrategy());

        $this->expectException(MappingException::class);
        (new Mapper())->map([
            'firstName' => 'camel',
            'first_name' => 'snake',
        ], $meta, $ctx);
    }

    /**
     * @throws MappingException
     */
    public function testLiteralUnmappedStringIsAcceptedAsPropertyValue(): void
    {
        $meta = $this->meta([$this->property('name')]);

        $mapped = (new Mapper())->map(['name' => '__unmapped__'], $meta, null);

        self::assertSame('__unmapped__', $mapped['name']);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testMapFallsBackToNamingStrategySourceKeyWhenNoMapFrom(): void
    {
        $meta = $this->meta([$this->property('fullName')]);
        $ctx = (new Context(false, false))->withNamingStrategy(new CamelCaseStrategy());

        $mapped = (new Mapper())->map(['full_name' => 'snake-value'], $meta, $ctx);

        self::assertSame('snake-value', $mapped['fullName']);
    }

    /**
     * @throws MappingException
     */
    public function testMapUsesRawPropertyNameWithoutNamingStrategy(): void
    {
        $meta = $this->meta([$this->property('name')]);

        $mapped = (new Mapper())->map(['name' => 'raw'], $meta, null);

        self::assertSame('raw', $mapped['name']);
    }

    /**
     * @throws MappingException
     */
    public function testMapSkipsPropertiesMissingFromSource(): void
    {
        $meta = $this->meta([$this->property('name')]);

        $mapped = (new Mapper())->map([], $meta, null);

        self::assertArrayNotHasKey('name', $mapped);
    }

    /**
     * @throws MappingException
     */
    public function testUnknownKeysReportsKeysNotBackingAnyProperty(): void
    {
        $meta = $this->meta([$this->property('name')]);

        $unknown = (new Mapper())->unknownKeys(['name' => 'x', 'bogus' => 'y'], $meta, null, null);

        self::assertSame(['bogus'], $unknown);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testUnknownKeysAllowsDiscriminatorFieldAndItsNamingVariants(): void
    {
        $meta = $this->meta([$this->property('name')]);
        $ctx = (new Context(false, false))->withNamingStrategy(new CamelCaseStrategy());

        $unknown = (new Mapper())->unknownKeys(
            ['name' => 'x', 'object_type' => 'circle'],
            $meta,
            $ctx,
            'objectType',
        );

        self::assertSame([], $unknown);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMissingUsesDefaultFromDetectsAttributeOnlyWhenValueAbsent(): void
    {
        $property = $this->property('age', [new DefaultFrom(method: 'fallback')]);
        $withoutDefaultFrom = $this->property('name');
        $mapper = new Mapper();

        self::assertTrue($mapper->missingUsesDefaultFrom($property, []));
        self::assertFalse($mapper->missingUsesDefaultFrom($property, ['age' => 5]));
        self::assertFalse($mapper->missingUsesDefaultFrom($withoutDefaultFrom, []));
    }

    /**
     * @throws MappingException
     */
    public function testMapSkipsHiddenPropertiesFromExternalSource(): void
    {
        $meta = $this->meta([
            PropertyMetaBuilder::make(
                name: 'role',
                type: TypeDescriptor::builtin('string'),
                hidden: PropertyMeta::HIDDEN,
            ),
            $this->property('name'),
        ]);

        $mapped = (new Mapper())->map(['role' => 'admin', 'name' => 'joo'], $meta, null);

        self::assertArrayNotHasKey('role', $mapped);
        self::assertSame('joo', $mapped['name']);
    }

    /**
     * @throws MappingException
     */
    public function testUnknownKeysTreatsHiddenPropertySourceKeysAsUnknown(): void
    {
        $meta = $this->meta([
            PropertyMetaBuilder::make(
                name: 'role',
                type: TypeDescriptor::builtin('string'),
                hidden: PropertyMeta::HIDDEN,
            ),
            $this->property('name'),
        ]);

        $unknown = (new Mapper())->unknownKeys(['role' => 'admin', 'name' => 'joo'], $meta, null, null);

        self::assertSame(['role'], $unknown);
    }
}
