<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureFloatDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class FloatCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsNativeFloat(): void
    {
        self::assertSame(3.14, CastingFixtureFloatDto::fromArray(['value' => 3.14])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesIntAndBool(): void
    {
        self::assertSame(5.0, CastingFixtureFloatDto::fromArray(['value' => 5])->value);
        self::assertSame(1.0, CastingFixtureFloatDto::fromArray(['value' => true])->value);
        self::assertSame(0.0, CastingFixtureFloatDto::fromArray(['value' => false])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesNumericStringIncludingScientificNotation(): void
    {
        self::assertSame(3.5, CastingFixtureFloatDto::fromArray(['value' => '3.5'])->value);
        self::assertSame(100.0, CastingFixtureFloatDto::fromArray(['value' => '1e2'])->value);
    }

    /**
     * C10 regression: a scientific-notation string large enough to overflow to
     * INF must be rejected rather than silently accepted as a float.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsScientificNotationStringOverflowingToInfinity(): void
    {
        try {
            CastingFixtureFloatDto::fromArray(['value' => '1e400']);
            self::fail('Expected CastException.');
        } catch (CastException $exception) {
            self::assertSame('float', $exception->expectedType);
        }
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsDirectInfinityAndNan(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureFloatDto::fromArray(['value' => INF]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsDirectNan(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureFloatDto::fromArray(['value' => NAN]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsNonNumericString(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureFloatDto::fromArray(['value' => 'abc']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsArrayValue(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureFloatDto::fromArray(['value' => ['3.14']]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNullRejectedOnNonNullableProperty(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureFloatDto::fromArray(['value' => null]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNullAllowedOnNullableProperty(): void
    {
        $dto = CastingFixtureFloatDto::fromArray(['value' => 1.0, 'optionalValue' => null]);
        self::assertNull($dto->optionalValue);
    }
}
