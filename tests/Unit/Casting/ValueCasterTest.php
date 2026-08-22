<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureStrictDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class ValueCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictTypeAllowsAnExactTypeMatch(): void
    {
        $dto = CastingFixtureStrictDto::fromArray(['value' => 5, 'other' => 6]);
        self::assertSame(5, $dto->value);
        self::assertSame(6, $dto->other);
    }

    /**
     * #[StrictType] bypasses the normal caster entirely: a numeric string
     * that IntCaster would happily coerce is rejected because it is not
     * already an int.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictTypeRejectsACoercibleButNonExactValueWithDefaultMessage(): void
    {
        try {
            CastingFixtureStrictDto::fromArray(['value' => '5', 'other' => 0]);
            self::fail('Expected CastException.');
        } catch (CastException $exception) {
            self::assertSame('Type mismatch', $exception->getMessage());
            self::assertSame('value', $exception->path);
            self::assertSame('int', $exception->expectedType);
            self::assertSame('string', $exception->givenType);
        }
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictTypeUsesTheAttributesCustomMessage(): void
    {
        try {
            CastingFixtureStrictDto::fromArray(['value' => 1, 'other' => '0']);
            self::fail('Expected CastException.');
        } catch (CastException $exception) {
            self::assertSame('Custom strict failure', $exception->getMessage());
            self::assertSame('other', $exception->path);
        }
    }
}
