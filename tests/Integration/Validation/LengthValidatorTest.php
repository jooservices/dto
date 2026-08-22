<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\NameDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class LengthValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAsciiWithinBoundsPasses(): void
    {
        $dto = NameDto::fromArray(['name' => 'Vu'], new Context(validationEnabled: true, sourceKeyOut: false));

        self::assertSame('Vu', $dto->name);
    }

    /**
     * Regression: a multi-byte string must be measured by character count
     * (mb_strlen), not by byte count (strlen), or valid UTF-8 input is
     * wrongly rejected as "too long".
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMultibyteStringIsMeasuredByCharacterNotByte(): void
    {
        // 5 four-byte emoji codepoints => 20 bytes, but 5 characters.
        $value = str_repeat('😀', 5);
        self::assertSame(20, strlen($value));

        $dto = NameDto::fromArray(['name' => $value], new Context(validationEnabled: true, sourceKeyOut: false));

        self::assertSame($value, $dto->name);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTooLongStringFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        NameDto::fromArray(['name' => 'this-is-too-long'], new Context(validationEnabled: true, sourceKeyOut: false));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testEmptyStringFailsMinLength(): void
    {
        $this->expectException(ValidationException::class);
        NameDto::fromArray(['name' => ''], new Context(validationEnabled: true, sourceKeyOut: false));
    }
}
