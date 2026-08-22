<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureIntDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class IntCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsNativeInt(): void
    {
        self::assertSame(42, CastingFixtureIntDto::fromArray(['value' => 42])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesFiniteFloatAndBool(): void
    {
        self::assertSame(3, CastingFixtureIntDto::fromArray(['value' => 3.9])->value);
        self::assertSame(1, CastingFixtureIntDto::fromArray(['value' => true])->value);
        self::assertSame(0, CastingFixtureIntDto::fromArray(['value' => false])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsInfiniteFloat(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureIntDto::fromArray(['value' => INF]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesDigitOnlyString(): void
    {
        self::assertSame(42, CastingFixtureIntDto::fromArray(['value' => '42'])->value);
        self::assertSame(-7, CastingFixtureIntDto::fromArray(['value' => '-7'])->value);
    }

    /**
     * Loose mode intentionally truncates a decimal numeric string (it merely
     * checks the value is numeric and finite, not that it is integral).
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testLooseModeTruncatesDecimalString(): void
    {
        self::assertSame(3, CastingFixtureIntDto::fromArray(['value' => '3.7'])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictModeRejectsDecimalString(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureIntDto::fromArray(['value' => '3.7'], Context::strict());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesScientificNotationStringWithoutFraction(): void
    {
        self::assertSame(1000, CastingFixtureIntDto::fromArray(['value' => '1e3'])->value);
        self::assertSame(100, CastingFixtureIntDto::fromArray(['value' => '1e2'])->value);
    }

    /**
     * C10-adjacent: a scientific-notation string that overflows to INF must be
     * rejected rather than silently truncated to an int.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsScientificNotationStringOverflowingToInfinity(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureIntDto::fromArray(['value' => '1e400']);
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
        CastingFixtureIntDto::fromArray(['value' => 'abc']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsEmptyString(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureIntDto::fromArray(['value' => '']);
    }

    /**
     * A digit-only string long enough to overflow PHP_INT_MAX is rejected
     * rather than silently clamped by PHP's own (int) cast.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsDigitStringOverflowingIntRange(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureIntDto::fromArray(['value' => '99999999999999999999']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsArrayValueInLooseMode(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureIntDto::fromArray(['value' => ['nope']]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayValueCoercedInPermissiveMode(): void
    {
        $dto = CastingFixtureIntDto::fromArray(['value' => ['a', 'b']], Context::permissive());
        self::assertSame(1, $dto->value);

        $dto = CastingFixtureIntDto::fromArray(['value' => []], Context::permissive());
        self::assertSame(0, $dto->value);
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
        CastingFixtureIntDto::fromArray(['value' => null]);
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
        $dto = CastingFixtureIntDto::fromArray(['value' => 1, 'optionalValue' => null]);
        self::assertNull($dto->optionalValue);
    }
}
