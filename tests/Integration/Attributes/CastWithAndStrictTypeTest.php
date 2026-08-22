<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureCastWith;
use JOOservices\Dto\Tests\Fixtures\AttrFixturePrefixed;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureStrict;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

/**
 * Real hydration coverage for CastWith (custom caster hook) and StrictType (exact-type-only
 * property).
 */
final class CastWithAndStrictTypeTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCastWithReplacesTheDefaultCastingLogicEntirely(): void
    {
        $dto = AttrFixtureCastWith::fromArray(['code' => 'abc']);

        self::assertSame('ABC', $dto->code);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCastWithOptionsAreSpreadIntoTheCasterConstructor(): void
    {
        $dto = AttrFixturePrefixed::fromArray(['value' => 'x']);

        self::assertSame('PRE-x', $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictTypeAcceptsAnExactTypeMatch(): void
    {
        $dto = AttrFixtureStrict::fromArray(['count' => 5]);

        self::assertSame(5, $dto->count);
    }

    /**
     * Unhappy: StrictType rejects anything that isn't already the exact native type, even a
     * numeric string that the normal IntCaster would happily coerce.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictTypeRejectsACoercibleButNonExactValue(): void
    {
        try {
            AttrFixtureStrict::fromArray(['count' => '5']);
            self::fail('Expected CastException.');
        } catch (CastException $exception) {
            self::assertSame('count must be a native int', $exception->getMessage());
            self::assertSame('count', $exception->path);
        }
    }
}
