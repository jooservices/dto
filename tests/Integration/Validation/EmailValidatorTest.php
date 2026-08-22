<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureEmailDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class EmailValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValidEmailPasses(): void
    {
        $dto = ValidationFixtureEmailDto::fromArray(
            ['email' => 'joo@example.com'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('joo@example.com', $dto->email);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidEmailFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureEmailDto::fromArray(
            ['email' => 'not-an-email'],
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
        $dto = ValidationFixtureEmailDto::fromArray(
            ['email' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertNull($dto->email);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testEmptyStringFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureEmailDto::fromArray(
            ['email' => ''],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
