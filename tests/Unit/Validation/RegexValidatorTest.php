<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRegexDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRegexInvalidPatternDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class RegexValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMatchingValuePasses(): void
    {
        $dto = ValidationFixtureRegexDto::fromArray(
            ['value' => 'abc'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('abc', $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNonMatchingValueFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureRegexDto::fromArray(
            ['value' => 'ABC123'],
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
        $dto = ValidationFixtureRegexDto::fromArray(
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
    public function testEmptyStringFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureRegexDto::fromArray(
            ['value' => ''],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * S1 ReDoS mitigation: input longer than the 4096-byte cap must be
     * rejected before it ever reaches preg_match().
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInputOverByteCapFailsValidation(): void
    {
        $tooLong = str_repeat('a', 4097);
        self::assertSame(4097, strlen($tooLong));

        $this->expectException(ValidationException::class);
        ValidationFixtureRegexDto::fromArray(
            ['value' => $tooLong],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * Boundary: exactly 4096 bytes is still allowed through to preg_match().
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInputAtByteCapPasses(): void
    {
        $atCap = str_repeat('a', 4096);
        self::assertSame(4096, strlen($atCap));

        $dto = ValidationFixtureRegexDto::fromArray(
            ['value' => $atCap],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame($atCap, $dto->value);
    }

    /**
     * Fail-closed: an invalid pattern makes preg_match() return false (with a
     * PHP warning, suppressed here since it is not under test), and
     * preg_last_error() is non-zero — the validator must reject the value
     * rather than silently accepting it.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidPatternFailsClosed(): void
    {
        $this->expectException(ValidationException::class);
        @ValidationFixtureRegexInvalidPatternDto::fromArray(
            ['value' => 'anything'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
