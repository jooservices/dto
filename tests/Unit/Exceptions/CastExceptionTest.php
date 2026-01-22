<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Tests\TestCase;
use RuntimeException;
use stdClass;

final class CastExceptionTest extends TestCase
{
    public function test_cannot_cast_with_string(): void
    {
        $value = $this->faker->word();
        $targetType = 'int';
        $path = $this->faker->word();

        $exception = CastException::cannotCast($value, $targetType, $path);

        $this->assertStringContainsString("Cannot cast value to '{$targetType}'", $exception->getMessage());
        $this->assertSame($path, $exception->path);
        $this->assertSame($targetType, $exception->expectedType);
        $this->assertSame('string', $exception->givenType);
        $this->assertSame($value, $exception->givenValue);
    }

    public function test_cannot_cast_with_array(): void
    {
        $value = [$this->faker->word(), $this->faker->randomNumber()];
        $targetType = 'string';

        $exception = CastException::cannotCast($value, $targetType);

        $this->assertSame('array', $exception->givenType);
    }

    public function test_cannot_cast_with_previous_exception(): void
    {
        $previous = new RuntimeException($this->faker->sentence());

        $exception = CastException::cannotCast(
            $this->faker->word(),
            'int',
            '',
            $previous,
        );

        $this->assertSame($previous, $exception->getPrevious());
    }

    public function test_invalid_enum_value(): void
    {
        $value = $this->faker->word();
        $enumClass = 'App\\Enums\\Status';
        $path = $this->faker->word();

        $exception = CastException::invalidEnumValue($value, $enumClass, $path);

        $this->assertStringContainsString("Invalid value for enum '{$enumClass}'", $exception->getMessage());
        $this->assertSame($path, $exception->path);
        $this->assertSame($enumClass, $exception->expectedType);
    }

    public function test_invalid_date_time_format(): void
    {
        $value = $this->faker->word();
        $format = 'Y-m-d';
        $path = $this->faker->word();

        $exception = CastException::invalidDateTimeFormat($value, $format, $path);

        $this->assertStringContainsString("Cannot parse date/time value with format '{$format}'", $exception->getMessage());
        $this->assertSame('DateTimeImmutable', $exception->expectedType);
    }

    public function test_no_caster_found(): void
    {
        $value = new stdClass;
        $targetType = 'CustomClass';
        $path = $this->faker->word();

        $exception = CastException::noCasterFound($value, $targetType, $path);

        $this->assertStringContainsString("No caster found for type '{$targetType}'", $exception->getMessage());
        $this->assertSame($path, $exception->path);
        $this->assertSame($targetType, $exception->expectedType);
    }

    public function test_cannot_cast_with_null(): void
    {
        $exception = CastException::cannotCast(null, 'string');

        $this->assertSame('null', $exception->givenType);
        $this->assertNull($exception->givenValue);
    }

    public function test_cannot_cast_with_object(): void
    {
        $value = new stdClass;

        $exception = CastException::cannotCast($value, 'string');

        $this->assertSame('stdClass', $exception->givenType);
    }

    public function test_cannot_cast_with_empty_path(): void
    {
        $exception = CastException::cannotCast($this->faker->word(), 'int');

        $this->assertSame('', $exception->path);
    }
}
