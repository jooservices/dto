<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureUrlDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class UrlValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValidUrlPasses(): void
    {
        $dto = ValidationFixtureUrlDto::fromArray(
            ['website' => 'https://example.com'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('https://example.com', $dto->website);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidUrlFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureUrlDto::fromArray(
            ['website' => 'not a url'],
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
        $dto = ValidationFixtureUrlDto::fromArray(
            ['website' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertNull($dto->website);
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
        ValidationFixtureUrlDto::fromArray(
            ['website' => ''],
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
    public function testFileSchemeIsRejected(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureUrlDto::fromArray(
            ['website' => 'file:///etc/passwd'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
