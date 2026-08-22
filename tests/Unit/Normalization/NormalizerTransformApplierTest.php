<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Normalization\NormalizerTransformApplier;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureUppercaseTransformer;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class NormalizerTransformApplierTest extends TestCase
{
    /**
     * @param  list<object>  $attributes
     */
    private function property(array $attributes): PropertyMeta
    {
        return PropertyMetaBuilder::make(
            name: 'value',
            type: TypeDescriptor::builtin('string'),
            attributes: $attributes,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testAppliesTransformWithAttributeBeforeTheRegistry(): void
    {
        $property = $this->property([new TransformWith(CoreFixtureUppercaseTransformer::class)]);

        $result = (new NormalizerTransformApplier())->apply('hello', $property, new TransformerRegistry());

        self::assertSame('HELLO', $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testTransformWithForwardsConstructorOptions(): void
    {
        $property = $this->property([
            new TransformWith(CoreFixtureUppercaseTransformer::class, ['>>']),
        ]);

        $result = (new NormalizerTransformApplier())->apply('hi', $property, new TransformerRegistry());

        self::assertSame('>>HI', $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testMultipleTransformWithAttributesRunInDeclarationOrder(): void
    {
        $property = $this->property([
            new TransformWith(CoreFixtureUppercaseTransformer::class, ['1-']),
            new TransformWith(CoreFixtureUppercaseTransformer::class, ['2-']),
        ]);

        $result = (new NormalizerTransformApplier())->apply('x', $property, new TransformerRegistry());

        self::assertSame('2-1-X', $result);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testTransformWithTargetNotImplementingTransformerInterfaceThrows(): void
    {
        $property = $this->property([new TransformWith(stdClass::class)]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('TransformWith class must implement TransformerInterface.');

        (new NormalizerTransformApplier())->apply('untouched', $property, new TransformerRegistry());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testNoAttributesStillRunsThroughTheRegistry(): void
    {
        $property = $this->property([]);

        $result = (new NormalizerTransformApplier())->apply('as-is', $property, new TransformerRegistry());

        self::assertSame('as-is', $result);
    }
}
