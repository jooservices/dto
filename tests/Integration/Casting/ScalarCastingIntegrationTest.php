<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureBoolDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureFloatDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureIntDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureLevel;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureLevelDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureStrictDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureStringDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureSuit;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureSuitDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureTypedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureUnionNumberDto;
use JOOservices\Dto\Tests\Fixtures\EnumHolderDto;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class ScalarCastingIntegrationTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testBoolHydrationCoercesCommonInputs(): void
    {
        $dto = CastingFixtureBoolDto::fromArray(['flag' => 1, 'optionalFlag' => '0']);

        self::assertTrue($dto->flag);
        self::assertFalse($dto->optionalFlag);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIntAndFloatHydrationCoercesNumericStrings(): void
    {
        $int = CastingFixtureIntDto::fromArray(['value' => '42']);
        $float = CastingFixtureFloatDto::fromArray(['value' => '3.14']);

        self::assertSame(42, $int->value);
        self::assertSame(3.14, $float->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStringHydrationCastsScalars(): void
    {
        $dto = CastingFixtureStringDto::fromArray(['value' => 123]);

        self::assertSame('123', $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testEnumUnionAndTypedArrayHydration(): void
    {
        $enum = EnumHolderDto::fromArray(['status' => 'active']);
        $union = CastingFixtureUnionNumberDto::fromArray(['value' => '7']);
        $array = CastingFixtureTypedArrayDto::fromArray(['numbers' => ['1', 2, 3]]);

        self::assertSame(Status::Active, $enum->status);
        self::assertSame(7, $union->value);
        self::assertSame([1, 2, 3], $array->numbers);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testBackedEnumHydration(): void
    {
        $suit = CastingFixtureSuitDto::fromArray(['suit' => CastingFixtureSuit::Hearts]);
        $level = CastingFixtureLevelDto::fromArray(['level' => CastingFixtureLevel::High->value]);

        self::assertSame(CastingFixtureSuit::Hearts, $suit->suit);
        self::assertSame(CastingFixtureLevel::High, $level->level);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStrictTypeRejectsCoercibleValues(): void
    {
        $this->expectException(CastException::class);

        CastingFixtureStrictDto::fromArray(['value' => '5']);
    }
}
