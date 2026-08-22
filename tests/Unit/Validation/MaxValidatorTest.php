<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureMaxDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class MaxValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValueBelowMaximumPasses(): void
    {
        $dto = ValidationFixtureMaxDto::fromArray(
            ['value' => '50'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('50', $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMaximumBoundaryIsInclusive(): void
    {
        $dto = ValidationFixtureMaxDto::fromArray(
            ['value' => '100'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('100', $dto->value);
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
        ValidationFixtureMaxDto::fromArray(
            ['value' => '101'],
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
        $dto = ValidationFixtureMaxDto::fromArray(
            ['value' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertNull($dto->value);
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

        ValidationFixtureMaxDto::fromArray(
            ['value' => 'not-a-number'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
