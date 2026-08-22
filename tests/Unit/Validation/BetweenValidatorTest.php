<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureBetweenDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class BetweenValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValueWithinRangePasses(): void
    {
        $dto = ValidationFixtureBetweenDto::fromArray(
            ['score' => '5'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('5', $dto->score);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMinBoundaryIsInclusive(): void
    {
        $dto = ValidationFixtureBetweenDto::fromArray(
            ['score' => '1'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('1', $dto->score);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMaxBoundaryIsInclusive(): void
    {
        $dto = ValidationFixtureBetweenDto::fromArray(
            ['score' => '10'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('10', $dto->score);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testBelowMinimumFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureBetweenDto::fromArray(
            ['score' => '0'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAboveMaximumFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureBetweenDto::fromArray(
            ['score' => '11'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNullValueSkipsValidation(): void
    {
        $dto = ValidationFixtureBetweenDto::fromArray(
            ['score' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertNull($dto->score);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNonNumericValueFailsValidation(): void
    {
        $this->expectException(ValidationException::class);

        ValidationFixtureBetweenDto::fromArray(
            ['score' => 'abc'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
