<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\TestCase;

final class ValidationExceptionTest extends TestCase
{
    public function test_construct(): void
    {
        $exception = new ValidationException('Validation failed');

        $this->assertSame('Validation failed', $exception->getMessage());
    }

    public function test_construct_with_path(): void
    {
        $exception = new ValidationException('Validation failed', 'user.name');

        $this->assertSame('user.name', $exception->getPath());
    }

    public function test_construct_with_all_parameters(): void
    {
        $exception = new ValidationException(
            message: 'Invalid value',
            path: 'user.age',
            expectedType: 'integer',
            givenType: 'string',
            givenValue: 'abc',
            code: 400,
        );

        $this->assertSame('Invalid value', $exception->getMessage());
        $this->assertSame('user.age', $exception->getPath());
        $this->assertSame(400, $exception->getCode());
    }

    public function test_from_violations(): void
    {
        $violations = [
            new RuleViolation('name', 'required', 'Name is required'),
            new RuleViolation('age', 'min', 'Age must be at least 18'),
        ];

        $exception = ValidationException::fromViolations('Validation failed', $violations, 'user');

        $this->assertSame('Validation failed', $exception->getMessage());
        $this->assertSame('user', $exception->getPath());
        $this->assertCount(2, $exception->getViolations());
    }

    public function test_add_violation(): void
    {
        $exception = new ValidationException('Validation failed');
        $violation = new RuleViolation('name', 'required', 'Name is required');

        $result = $exception->addViolation($violation);

        $this->assertSame($exception, $result);
        $this->assertCount(1, $exception->getViolations());
    }

    public function test_add_violations(): void
    {
        $exception = new ValidationException('Validation failed');
        $violations = [
            new RuleViolation('name', 'required', 'Name is required'),
            new RuleViolation('age', 'min', 'Age must be at least 18'),
        ];

        $result = $exception->addViolations($violations);

        $this->assertSame($exception, $result);
        $this->assertCount(2, $exception->getViolations());
    }

    public function test_get_violations(): void
    {
        $violations = [
            new RuleViolation('name', 'required', 'Name is required'),
        ];

        $exception = ValidationException::fromViolations('Validation failed', $violations);
        $result = $exception->getViolations();

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(RuleViolation::class, $result[0]);
    }

    public function test_has_violations_returns_true_when_violations_exist(): void
    {
        $exception = new ValidationException('Validation failed');
        $exception->addViolation(new RuleViolation('name', 'required', 'Required'));

        $this->assertTrue($exception->hasViolations());
    }

    public function test_has_violations_returns_false_when_no_violations(): void
    {
        $exception = new ValidationException('Validation failed');

        $this->assertFalse($exception->hasViolations());
    }

    public function test_get_violation_count(): void
    {
        $exception = new ValidationException('Validation failed');
        $this->assertSame(0, $exception->getViolationCount());

        $exception->addViolation(new RuleViolation('name', 'required', 'Required'));
        $this->assertSame(1, $exception->getViolationCount());

        $exception->addViolation(new RuleViolation('age', 'min', 'Too young'));
        $this->assertSame(2, $exception->getViolationCount());
    }

    public function test_get_full_message(): void
    {
        $exception = new ValidationException('Validation failed');

        $message = $exception->getFullMessage();

        $this->assertStringContainsString('Validation failed', $message);
    }

    public function test_get_full_message_with_violations(): void
    {
        $exception = new ValidationException('Validation failed');
        $exception->addViolation(new RuleViolation('name', 'required', 'Name is required'));
        $exception->addViolation(new RuleViolation('age', 'min', 'Age must be at least 18'));

        $message = $exception->getFullMessage();

        $this->assertStringContainsString('Validation failed', $message);
        $this->assertStringContainsString('2 violation(s)', $message);
        $this->assertStringContainsString('Name is required', $message);
        $this->assertStringContainsString('Age must be at least 18', $message);
    }

    public function test_get_full_message_includes_path(): void
    {
        $exception = new ValidationException('Validation failed', 'user.profile');

        $message = $exception->getFullMessage();

        $this->assertStringContainsString('user.profile', $message);
    }

    public function test_add_multiple_violations_individually(): void
    {
        $exception = new ValidationException('Validation failed');

        $exception
            ->addViolation(new RuleViolation('name', 'required', 'Required'))
            ->addViolation(new RuleViolation('age', 'min', 'Too young'))
            ->addViolation(new RuleViolation('email', 'email', 'Invalid email'));

        $this->assertCount(3, $exception->getViolations());
    }

    public function test_empty_violations_array(): void
    {
        $exception = new ValidationException('Validation failed');
        $exception->addViolations([]);

        $this->assertFalse($exception->hasViolations());
        $this->assertCount(0, $exception->getViolations());
    }
}
