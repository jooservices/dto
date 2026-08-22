<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Normalization\NormalizerValueTransformer;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use JsonSerializable;
use stdClass;

final class NormalizerValueTransformerTest extends TestCase
{
    private function property(?string $redactWith = null): PropertyMeta
    {
        return PropertyMetaBuilder::make(
            name: 'value',
            type: TypeDescriptor::builtin('float'),
            redactWith: $redactWith,
        );
    }

    private function transformer(): NormalizerValueTransformer
    {
        $registry = new TransformerRegistry();
        $registry->register(new EnumTransformer());

        return new NormalizerValueTransformer($registry);
    }

    /**
     * @throws HydrationException
     */
    public function testAssertFiniteFloatAllowsOrdinaryFloats(): void
    {
        $this->transformer()->assertFiniteFloat(1.5, $this->property());
        self::addToAssertionCount(1);
    }

    /**
     * @throws HydrationException
     */
    public function testAssertFiniteFloatIgnoresNonFloatValues(): void
    {
        $this->transformer()->assertFiniteFloat('not-a-float', $this->property());
        self::addToAssertionCount(1);
    }

    /**
     * @throws HydrationException
     */
    public function testAssertFiniteFloatRejectsInfinity(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('INF and NaN cannot be serialized to JSON.');

        $this->transformer()->assertFiniteFloat(INF, $this->property());
    }

    /**
     * @throws HydrationException
     */
    public function testAssertFiniteFloatRejectsNan(): void
    {
        $this->expectException(HydrationException::class);

        $this->transformer()->assertFiniteFloat(NAN, $this->property());
    }

    /**
     * Redacted properties must never leak the raw non-finite value in the exception payload.
     */
    public function testAssertFiniteFloatUsesRedactedValueInException(): void
    {
        try {
            $this->transformer()->assertFiniteFloat(NAN, $this->property('***'));
            self::fail('Expected HydrationException.');
        } catch (HydrationException $exception) {
            self::assertSame('***', $exception->givenValue);
        }
    }

    public function testNormalizeArrayItemsTransformsScalarItemsThroughTheRegistry(): void
    {
        $result = $this->transformer()->normalizeArrayItems(
            ['a' => Status::Active, 'b' => 'plain'],
            $this->property(),
            static fn(mixed $item, PropertyMeta $property): mixed => $item,
        );

        self::assertSame('active', $result['a']);
        self::assertSame('plain', $result['b']);
    }

    public function testNormalizeArrayItemsDelegatesObjectItemsToTheCallback(): void
    {
        $seenProperty = null;
        $result = $this->transformer()->normalizeArrayItems(
            ['x' => new stdClass()],
            $this->property(),
            function (mixed $item, PropertyMeta $property) use (&$seenProperty): mixed {
                $seenProperty = $property;

                return 'converted';
            },
        );

        self::assertSame('converted', $result['x']);
        self::assertSame('value', $seenProperty?->name);
    }

    public function testJsonSerializeIfNeededUnwrapsJsonSerializableValues(): void
    {
        $value = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['wrapped' => true];
            }
        };

        self::assertSame(['wrapped' => true], $this->transformer()->jsonSerializeIfNeeded($value));
    }

    public function testJsonSerializeIfNeededPassesThroughPlainValues(): void
    {
        self::assertSame('plain', $this->transformer()->jsonSerializeIfNeeded('plain'));
    }

    public function testFallbackForUndescribableNestedUsesJsonSerializeForThirdPartyObjects(): void
    {
        $value = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['wrapped' => true];
            }
        };

        self::assertSame(
            ['wrapped' => true],
            $this->transformer()->fallbackForUndescribableNested($value),
        );
    }

    public function testFallbackForUndescribableNestedReturnsPlaceholderForPlainObjects(): void
    {
        $value = new stdClass();

        self::assertSame(
            '[object stdClass]',
            $this->transformer()->fallbackForUndescribableNested($value),
        );
    }
}
