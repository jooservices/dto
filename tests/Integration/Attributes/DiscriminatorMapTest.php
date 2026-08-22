<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureCircle;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureShape;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureSquare;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

/**
 * Real hydration coverage for DiscriminatorMap-driven polymorphism via AttrFixtureShape
 * (circle/square/base/invalid map entries).
 */
final class DiscriminatorMapTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDiscriminatorValueSelectsTheMappedSubclass(): void
    {
        $shape = AttrFixtureShape::from(['type' => 'circle', 'radius' => 2.5]);

        self::assertInstanceOf(AttrFixtureCircle::class, $shape);
        self::assertSame(2.5, $shape->radius);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDifferentDiscriminatorValueSelectsADifferentSubclass(): void
    {
        $shape = AttrFixtureShape::from(['type' => 'square', 'side' => 4.0]);

        self::assertInstanceOf(AttrFixtureSquare::class, $shape);
        self::assertSame(4.0, $shape->side);
    }

    /**
     * When the map resolves back to the same class the hydration was invoked on, the base
     * class is instantiated directly (DiscriminatorResolver's same-class branch).
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSameClassMapEntryInstantiatesTheBaseClassItself(): void
    {
        $shape = AttrFixtureShape::from(['type' => 'base']);

        self::assertSame(AttrFixtureShape::class, $shape::class);
        self::assertSame('base', $shape->type);
    }

    /**
     * Weird/edge finding: serializing a hydrated subtype must preserve the discriminator
     * field for round-trip via the base class.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSerializingAHydratedSubtypePreservesTheDiscriminatorField(): void
    {
        $shape = AttrFixtureShape::from(['type' => 'circle', 'radius' => 1.0]);

        $array = $shape->toArray();

        self::assertSame('circle', $array['type']);
        self::assertSame(1.0, $array['radius']);

        $roundTrip = AttrFixtureShape::from($array);
        self::assertInstanceOf(AttrFixtureCircle::class, $roundTrip);
    }

    /**
     * Unhappy: input missing the discriminator field entirely.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMissingDiscriminatorFieldRaisesHydrationException(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Missing discriminator field.');

        AttrFixtureShape::from(['radius' => 1.0]);
    }

    /**
     * Unhappy: a discriminator value that isn't in the map at all.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnknownDiscriminatorValueRaisesHydrationException(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Unknown discriminator value.');

        AttrFixtureShape::from(['type' => 'triangle']);
    }

    /**
     * Unhappy: a map entry whose target class is not actually a subclass of the base DTO.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMapEntryTargetingANonSubclassRaisesHydrationException(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Discriminator target is not a DTO subclass.');

        AttrFixtureShape::from(['type' => 'invalid']);
    }
}
