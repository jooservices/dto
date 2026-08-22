<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\StrictType;
use JOOservices\Dto\Casting\AttributeCastResolver;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureUppercaseCaster;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class AttributeCastResolverTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCastWithReturnsNullWhenPropertyHasNoCastWithAttribute(): void
    {
        $resolver = new AttributeCastResolver();
        $property = PropertyMetaBuilder::make(
            name: 'x',
            type: TypeDescriptor::builtin('string'),
        );

        self::assertNull($resolver->castWith($property, 'value', null));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCastWithDelegatesToTheReferencedCaster(): void
    {
        $resolver = new AttributeCastResolver();
        $property = PropertyMetaBuilder::make(
            name: 'label',
            type: TypeDescriptor::builtin('string'),
            attributes: [new CastWith(CastingFixtureUppercaseCaster::class)],
        );

        self::assertSame('HELLO', $resolver->castWith($property, 'hello', null));
    }

    public function testFindStrictTypeReturnsNullWhenAbsent(): void
    {
        $resolver = new AttributeCastResolver();
        $property = PropertyMetaBuilder::make(
            name: 'x',
            type: TypeDescriptor::builtin('int'),
        );

        self::assertNull($resolver->findStrictType($property));
    }

    public function testFindStrictTypeReturnsTheAttributeWhenPresent(): void
    {
        $resolver = new AttributeCastResolver();
        $strict = new StrictType(message: 'nope');
        $property = PropertyMetaBuilder::make(
            name: 'x',
            type: TypeDescriptor::builtin('int'),
            attributes: [$strict],
        );

        self::assertSame($strict, $resolver->findStrictType($property));
    }

    public function testStrictFailureBuildsCastExceptionFromPropertyAndValue(): void
    {
        $resolver = new AttributeCastResolver();
        $property = PropertyMetaBuilder::make(
            name: 'age',
            type: TypeDescriptor::builtin('int'),
        );
        $strict = new StrictType(message: 'Strict mismatch');

        $exception = $resolver->strictFailure($property, '30', $strict);

        self::assertSame('Strict mismatch', $exception->getMessage());
        self::assertSame('age', $exception->path);
        self::assertSame('int', $exception->expectedType);
        self::assertSame('string', $exception->givenType);
        self::assertSame('30', $exception->givenValue);
    }

    public function testStrictFailureRedactsGivenValueWhenPropertyRedacts(): void
    {
        $resolver = new AttributeCastResolver();
        $property = PropertyMetaBuilder::make(
            name: 'pin',
            type: TypeDescriptor::builtin('int'),
            redactWith: '***REDACTED***',
        );
        $strict = new StrictType();

        $exception = $resolver->strictFailure($property, '1234', $strict);

        self::assertSame('***REDACTED***', $exception->givenValue);
    }
}
