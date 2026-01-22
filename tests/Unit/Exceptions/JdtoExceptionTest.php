<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\JdtoException;
use JOOservices\Dto\Tests\TestCase;
use RuntimeException;

final class JdtoExceptionTest extends TestCase
{
    public function test_constructor_with_all_parameters(): void
    {
        $message = $this->faker->sentence();
        $path = $this->faker->word().'.'.$this->faker->word();
        $expectedType = $this->faker->word();
        $givenType = $this->faker->word();
        $givenValue = $this->faker->randomNumber();
        $code = $this->faker->randomNumber(3);

        $exception = new JdtoException(
            $message,
            $path,
            $expectedType,
            $givenType,
            $givenValue,
            $code,
        );

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($path, $exception->path);
        $this->assertSame($expectedType, $exception->expectedType);
        $this->assertSame($givenType, $exception->givenType);
        $this->assertSame($givenValue, $exception->givenValue);
        $this->assertSame($code, $exception->getCode());
    }

    public function test_constructor_with_minimal_parameters(): void
    {
        $message = $this->faker->sentence();

        $exception = new JdtoException($message);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame('', $exception->path);
        $this->assertNull($exception->expectedType);
        $this->assertNull($exception->givenType);
        $this->assertNull($exception->givenValue);
    }

    public function test_with_path(): void
    {
        $exception = new JdtoException($this->faker->sentence());
        $newPath = $this->faker->word();

        $newException = $exception->withPath($newPath);

        $this->assertNotSame($exception, $newException);
        $this->assertSame($newPath, $newException->path);
        $this->assertSame('', $exception->path);
    }

    public function test_prepend_path_on_empty_path(): void
    {
        $exception = new JdtoException($this->faker->sentence());
        $segment = $this->faker->word();

        $newException = $exception->prependPath($segment);

        $this->assertSame($segment, $newException->path);
    }

    public function test_prepend_path_on_existing_path(): void
    {
        $existingPath = $this->faker->word();
        $segment = $this->faker->word();
        $exception = new JdtoException($this->faker->sentence(), $existingPath);

        $newException = $exception->prependPath($segment);

        $this->assertSame($segment.'.'.$existingPath, $newException->path);
    }

    public function test_get_full_message_without_path(): void
    {
        $message = $this->faker->sentence();
        $exception = new JdtoException($message);

        $this->assertSame($message, $exception->getFullMessage());
    }

    public function test_get_full_message_with_path(): void
    {
        $message = $this->faker->sentence();
        $path = $this->faker->word();
        $exception = new JdtoException($message, $path);

        $this->assertStringContainsString($message, $exception->getFullMessage());
        $this->assertStringContainsString("at path '{$path}'", $exception->getFullMessage());
    }

    public function test_get_full_message_with_type_info(): void
    {
        $message = $this->faker->sentence();
        $expectedType = 'string';
        $givenType = 'int';
        $exception = new JdtoException($message, '', $expectedType, $givenType);

        $fullMessage = $exception->getFullMessage();

        $this->assertStringContainsString($message, $fullMessage);
        $this->assertStringContainsString("expected: {$expectedType}", $fullMessage);
        $this->assertStringContainsString("given: {$givenType}", $fullMessage);
    }

    public function test_with_null_given_value(): void
    {
        $exception = new JdtoException(
            $this->faker->sentence(),
            $this->faker->word(),
            'string',
            'null',
            null,
        );

        $this->assertNull($exception->givenValue);
    }

    public function test_with_array_given_value(): void
    {
        $arrayValue = [$this->faker->word(), $this->faker->word()];
        $exception = new JdtoException(
            $this->faker->sentence(),
            '',
            'string',
            'array',
            $arrayValue,
        );

        $this->assertSame($arrayValue, $exception->givenValue);
    }

    public function test_with_previous_exception(): void
    {
        $previousMessage = $this->faker->sentence();
        $previous = new RuntimeException($previousMessage);

        $exception = new JdtoException(
            $this->faker->sentence(),
            previous: $previous,
        );

        $this->assertSame($previous, $exception->getPrevious());
    }
}
