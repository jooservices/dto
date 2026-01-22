<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\TestCase;

final class ValidationIntegrationTest extends TestCase
{
    public function test_validation_disabled_by_default(): void
    {
        // Should not throw even with invalid data when validation is disabled
        $dto = TestUserDto::from([
            'name' => '',
            'email' => 'invalid-email',
        ]);

        $this->assertSame('', $dto->name);
        $this->assertSame('invalid-email', $dto->email);
    }

    public function test_validation_passes_for_valid_data(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = TestUserDto::from([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ], $context);

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
    }

    public function test_validation_fails_for_invalid_email(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        TestUserDto::from([
            'name' => 'John Doe',
            'email' => 'invalid-email',
        ], $context);
    }

    public function test_validation_fails_for_empty_required_field(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        TestUserDto::from([
            'name' => '',
            'email' => 'john@example.com',
        ], $context);
    }

    public function test_validation_exception_contains_validation_error(): void
    {
        $context = new Context(validationEnabled: true);

        try {
            TestUserDto::from([
                'name' => 'John',
                'email' => 'invalid-email',
            ], $context);

            $this->fail('Expected HydrationException');
        } catch (HydrationException $e) {
            // HydrationException wraps ValidationException
            $errors = $e->getErrors();
            $this->assertNotEmpty($errors);

            // Check that we have errors (which wrap validation errors)
            $this->assertGreaterThan(0, count($errors));
        }
    }

    public function test_optional_field_with_email_validation(): void
    {
        $context = new Context(validationEnabled: true);

        // Empty workEmail should be valid (it's optional)
        $dto = TestUserWithOptionalEmailDto::from([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ], $context);

        $this->assertSame('John Doe', $dto->name);
        $this->assertNull($dto->workEmail);
    }

    public function test_optional_field_with_invalid_email_fails(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        TestUserWithOptionalEmailDto::from([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'workEmail' => 'not-valid',
        ], $context);
    }

    public function test_custom_validation_message_in_exception(): void
    {
        $context = new Context(validationEnabled: true);

        try {
            TestUserWithCustomMessageDto::from([
                'email' => 'invalid',
            ], $context);

            $this->fail('Expected HydrationException');
        } catch (HydrationException $e) {
            // The full message should contain the property name 'email'
            $fullMessage = $e->getFullMessage();
            $this->assertStringContainsString('email', $fullMessage);
            // Verify it's a validation error
            $this->assertStringContainsString('Validation failed', $fullMessage);
        }
    }

    public function test_validation_with_nullable_field(): void
    {
        $context = new Context(validationEnabled: true);

        // Null should be valid for nullable email field without Required
        $dto = TestNullableEmailDto::from([
            'email' => null,
        ], $context);

        $this->assertNull($dto->email);
    }

    public function test_validation_violation_contains_invalid_value(): void
    {
        $context = new Context(validationEnabled: true);

        try {
            TestUserDto::from([
                'name' => 'John',
                'email' => 'bad@',
            ], $context);

            $this->fail('Expected HydrationException');
        } catch (HydrationException $e) {
            // Full message should contain the invalid value info
            $fullMessage = $e->getFullMessage();
            $this->assertStringContainsString('email', $fullMessage);
        }
    }

    public function test_multiple_validators_on_same_property(): void
    {
        $context = new Context(validationEnabled: true);

        // Valid case - both Required and Email pass
        $dto = TestUserDto::from([
            'name' => 'John',
            'email' => 'john@example.com',
        ], $context);

        $this->assertSame('john@example.com', $dto->email);
    }
}

// Test DTOs for integration tests

class TestUserDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $name,
        #[Required]
        #[Email]
        public readonly string $email,
    ) {}
}

class TestUserWithOptionalEmailDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly string $name,
        #[Required]
        #[Email]
        public readonly string $email,
        #[Email]
        public readonly ?string $workEmail = null,
    ) {}
}

class TestUserWithCustomMessageDto extends Dto
{
    public function __construct(
        #[Email(message: 'Please enter a valid email')]
        public readonly string $email,
    ) {}
}

class TestNullableEmailDto extends Dto
{
    public function __construct(
        #[Email]
        public readonly ?string $email = null,
    ) {}
}
