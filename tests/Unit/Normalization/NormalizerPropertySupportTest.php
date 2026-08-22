<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\MapTo;
use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Hydration\Naming\CamelCaseStrategy;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Normalization\NormalizerPropertySupport;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class NormalizerPropertySupportTest extends TestCase
{
    /**
     * @param  list<object>  $attributes
     */
    private function property(string $name, array $attributes = [], int $hidden = 0): PropertyMeta
    {
        return PropertyMetaBuilder::make(
            name: $name,
            type: TypeDescriptor::builtin('string'),
            attributes: $attributes,
            hidden: $hidden,
        );
    }

    public function testIsHiddenTrueViaHiddenFlag(): void
    {
        $support = new NormalizerPropertySupport();

        self::assertTrue($support->isHidden($this->property('secret', hidden: PropertyMeta::HIDDEN)));
    }

    public function testIsHiddenTrueViaHiddenAttribute(): void
    {
        $support = new NormalizerPropertySupport();

        self::assertTrue($support->isHidden($this->property('secret', [new Hidden()])));
    }

    public function testIsHiddenFalseForOrdinaryProperty(): void
    {
        $support = new NormalizerPropertySupport();

        self::assertFalse($support->isHidden($this->property('name')));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testOutputKeyPrefersMapToOverEverythingElse(): void
    {
        $support = new NormalizerPropertySupport();
        $property = $this->property('name', [new MapTo('full_name'), new MapFrom('original_name')]);
        $ctx = (new Context(false, true))->withNamingStrategy(new CamelCaseStrategy());

        self::assertSame('full_name', $support->outputKey($property, $ctx));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testOutputKeyUsesNamingStrategyWhenSourceKeyOutIsRequested(): void
    {
        $support = new NormalizerPropertySupport();
        $property = $this->property('fullName');
        $ctx = (new Context(false, true))->withNamingStrategy(new CamelCaseStrategy());

        self::assertSame('full_name', $support->outputKey($property, $ctx));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testOutputKeyUsesMapFromKeyWhenSourceKeyOutRequestedWithoutNamingStrategy(): void
    {
        $support = new NormalizerPropertySupport();
        $property = $this->property('name', [new MapFrom('original_name')]);
        $ctx = new Context(false, true);

        self::assertSame('original_name', $support->outputKey($property, $ctx));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testOutputKeyFallsBackToPropertyNameByDefault(): void
    {
        $support = new NormalizerPropertySupport();
        $property = $this->property('name', [new MapFrom('original_name')]);
        $ctx = new Context(false, false);

        self::assertSame('name', $support->outputKey($property, $ctx));
    }

    public function testEmitDiscriminatorAddsFieldWhenMetaMatchesAMapEntry(): void
    {
        $support = new NormalizerPropertySupport();
        $meta = new ClassMeta(
            className: UserDto::class,
            properties: [],
            ctorParams: [],
            hasConstructor: true,
            discriminator: new DiscriminatorMap(field: 'kind', map: ['circle' => UserDto::class]),
        );

        self::assertSame(['label' => 'x', 'kind' => 'circle'], $support->emitDiscriminator($meta, ['label' => 'x']));
    }

    public function testEmitDiscriminatorLeavesOutputUnchangedWhenNoDiscriminatorDeclared(): void
    {
        $support = new NormalizerPropertySupport();
        $meta = new ClassMeta(className: UserDto::class, properties: [], ctorParams: [], hasConstructor: true);

        self::assertSame(['label' => 'x'], $support->emitDiscriminator($meta, ['label' => 'x']));
    }

    public function testEmitDiscriminatorLeavesOutputUnchangedWhenNoMapEntryMatchesTheClass(): void
    {
        $support = new NormalizerPropertySupport();
        $meta = new ClassMeta(
            className: UserDto::class,
            properties: [],
            ctorParams: [],
            hasConstructor: true,
            discriminator: new DiscriminatorMap(field: 'kind', map: ['square' => AddressDto::class]),
        );

        self::assertSame(['label' => 'x'], $support->emitDiscriminator($meta, ['label' => 'x']));
    }

    public function testMergeLazySkipsInstancesNotImplementingComputesLazyProperties(): void
    {
        $support = new NormalizerPropertySupport();
        $options = (new SerializationOptions())->withIncludeLazy([]);

        $result = $support->mergeLazy(new stdClass(), ['x' => 1], $options, new TransformerRegistry());

        self::assertSame(['x' => 1], $result);
    }

    public function testMergeLazyIsNoOpWhenIncludeLazyIsNull(): void
    {
        $support = new NormalizerPropertySupport();
        $instance = new class implements ComputesLazyProperties {
            public function computeLazyProperties(): array
            {
                return ['lazy' => 'value'];
            }
        };

        $result = $support->mergeLazy($instance, ['x' => 1], new SerializationOptions(), new TransformerRegistry());

        self::assertSame(['x' => 1], $result);
    }
}
