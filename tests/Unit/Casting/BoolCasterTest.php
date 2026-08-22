<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureBoolDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class BoolCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsNativeBooleans(): void
    {
        self::assertTrue(CastingFixtureBoolDto::fromArray(['flag' => true])->flag);
        self::assertFalse(CastingFixtureBoolDto::fromArray(['flag' => false])->flag);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNumericZeroAndNonZeroAreCoerced(): void
    {
        self::assertTrue(CastingFixtureBoolDto::fromArray(['flag' => 1])->flag);
        self::assertFalse(CastingFixtureBoolDto::fromArray(['flag' => 0])->flag);
        self::assertFalse(CastingFixtureBoolDto::fromArray(['flag' => 0.0])->flag);
        self::assertTrue(CastingFixtureBoolDto::fromArray(['flag' => 0.1])->flag);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTruthyStringVariantsAreCaseAndWhitespaceInsensitive(): void
    {
        foreach (['true', 'TRUE', '1', 'yes', 'YES', 'on', ' on '] as $value) {
            self::assertTrue(CastingFixtureBoolDto::fromArray(['flag' => $value])->flag, $value);
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
    public function testFalsyStringVariantsIncludingEmptyString(): void
    {
        foreach (['false', 'FALSE', '0', 'no', 'off', ''] as $value) {
            self::assertFalse(CastingFixtureBoolDto::fromArray(['flag' => $value])->flag, $value);
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
    public function testUnrecognizedStringRejectedInLooseMode(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureBoolDto::fromArray(['flag' => 'maybe']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNonScalarValueRejectedInLooseMode(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureBoolDto::fromArray(['flag' => ['nope']]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNonScalarValueCoercedInPermissiveMode(): void
    {
        $dto = CastingFixtureBoolDto::fromArray(['flag' => ['nope']], Context::permissive());
        self::assertTrue($dto->flag);

        $dto = CastingFixtureBoolDto::fromArray(['flag' => []], Context::permissive());
        self::assertFalse($dto->flag);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnrecognizedStringCoercedInPermissiveMode(): void
    {
        $dto = CastingFixtureBoolDto::fromArray(['flag' => 'maybe'], Context::permissive());
        self::assertTrue($dto->flag);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNullRejectedOnNonNullablePropertyInLooseMode(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureBoolDto::fromArray(['flag' => null]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNullCoercedToFalseOnNonNullablePropertyInPermissiveMode(): void
    {
        $dto = CastingFixtureBoolDto::fromArray(['flag' => null, 'optionalFlag' => null], Context::permissive());
        self::assertFalse($dto->flag);
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
        $dto = CastingFixtureBoolDto::fromArray(['flag' => true, 'optionalFlag' => null]);
        self::assertNull($dto->optionalFlag);
    }
}
