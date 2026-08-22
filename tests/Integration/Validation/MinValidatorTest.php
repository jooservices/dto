<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureMinDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class MinValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValueAboveMinimumPasses(): void
    {
        $dto = ValidationFixtureMinDto::fromArray(
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
    public function testMinimumBoundaryIsInclusive(): void
    {
        $dto = ValidationFixtureMinDto::fromArray(
            ['value' => '10'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('10', $dto->value);
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
        ValidationFixtureMinDto::fromArray(
            ['value' => '9'],
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
        $dto = ValidationFixtureMinDto::fromArray(
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

        ValidationFixtureMinDto::fromArray(
            ['value' => 'not-a-number'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
