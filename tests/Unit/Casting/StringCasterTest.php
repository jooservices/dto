<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureStringDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class StringCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsNativeString(): void
    {
        self::assertSame('hello', CastingFixtureStringDto::fromArray(['value' => 'hello'])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesIntAndFloat(): void
    {
        self::assertSame('42', CastingFixtureStringDto::fromArray(['value' => 42])->value);
        self::assertSame('3.5', CastingFixtureStringDto::fromArray(['value' => 3.5])->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCoercesBool(): void
    {
        self::assertSame('1', CastingFixtureStringDto::fromArray(['value' => true])->value);
        self::assertSame('', CastingFixtureStringDto::fromArray(['value' => false])->value);
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
        CastingFixtureStringDto::fromArray(['value' => INF]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsNan(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureStringDto::fromArray(['value' => NAN]);
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
        CastingFixtureStringDto::fromArray(['value' => ['nope']]);
    }

    /**
     * Unlike Bool/Int/Float, StringCaster does not consult the cast mode at
     * all: an array is rejected even under a permissive Context.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsArrayValueEvenInPermissiveMode(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureStringDto::fromArray(['value' => ['nope']], Context::permissive());
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
        CastingFixtureStringDto::fromArray(['value' => null]);
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
        $dto = CastingFixtureStringDto::fromArray(['value' => 'x', 'optionalValue' => null]);
        self::assertNull($dto->optionalValue);
    }
}
