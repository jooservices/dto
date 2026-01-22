<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\JdtoException;
use JOOservices\Dto\Tests\TestCase;

final class HydrationExceptionTest extends TestCase
{
    public function test_add_single_error(): void
    {
        $exception = new HydrationException($this->faker->sentence());
        $error = new JdtoException($this->faker->sentence());

        $exception->addError($error);

        $this->assertCount(1, $exception->getErrors());
        $this->assertTrue($exception->hasNestedErrors());
    }

    public function test_add_multiple_errors(): void
    {
        $exception = new HydrationException($this->faker->sentence());

        $errors = [
            new JdtoException($this->faker->sentence()),
            new JdtoException($this->faker->sentence()),
            new JdtoException($this->faker->sentence()),
        ];

        $exception->addErrors($errors);

        $this->assertCount(3, $exception->getErrors());
        $this->assertSame(3, $exception->getErrorCount());
    }

    public function test_has_nested_errors_returns_false_when_empty(): void
    {
        $exception = new HydrationException($this->faker->sentence());

        $this->assertFalse($exception->hasNestedErrors());
        $this->assertSame(0, $exception->getErrorCount());
    }

    public function test_from_errors_factory(): void
    {
        $message = $this->faker->sentence();
        $path = $this->faker->word();
        $errors = [
            new JdtoException($this->faker->sentence()),
            new JdtoException($this->faker->sentence()),
        ];

        $exception = HydrationException::fromErrors($message, $errors, $path);

        $this->assertSame($message, $exception->getMessage());
        $this->assertSame($path, $exception->path);
        $this->assertCount(2, $exception->getErrors());
    }

    public function test_get_full_message_with_nested_errors(): void
    {
        $mainMessage = $this->faker->sentence();
        $exception = new HydrationException($mainMessage);

        $error1Message = $this->faker->sentence();
        $error2Message = $this->faker->sentence();

        $exception->addError(new JdtoException($error1Message));
        $exception->addError(new JdtoException($error2Message));

        $fullMessage = $exception->getFullMessage();

        $this->assertStringContainsString($mainMessage, $fullMessage);
        $this->assertStringContainsString('[2 nested error(s)]', $fullMessage);
        $this->assertStringContainsString($error1Message, $fullMessage);
        $this->assertStringContainsString($error2Message, $fullMessage);
    }

    public function test_fluent_interface(): void
    {
        $exception = new HydrationException($this->faker->sentence());

        $result = $exception
            ->addError(new JdtoException($this->faker->sentence()))
            ->addError(new JdtoException($this->faker->sentence()));

        $this->assertSame($exception, $result);
        $this->assertCount(2, $exception->getErrors());
    }

    public function test_add_errors_fluent_interface(): void
    {
        $exception = new HydrationException($this->faker->sentence());
        $errors = [
            new JdtoException($this->faker->sentence()),
        ];

        $result = $exception->addErrors($errors);

        $this->assertSame($exception, $result);
    }
}
