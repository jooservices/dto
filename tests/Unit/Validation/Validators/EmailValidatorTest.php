<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\Validators\EmailValidator;

final class EmailValidatorTest extends TestCase
{
    private EmailValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new EmailValidator;
    }

    public function test_supports_returns_true_when_email_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertTrue($this->validator->supports($property, 'test@example.com'));
    }

    public function test_supports_returns_false_when_no_email_attribute(): void
    {
        $property = $this->createPropertyMeta('name', []);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('name', [new Required]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_for_valid_email(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'john@example.com', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_valid_email_with_subdomain(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'john@mail.example.com', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_valid_email_with_plus(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'john+newsletter@example.com', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_null_value(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        // Null is handled by RequiredValidator
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_empty_string(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        // Empty string is valid (use Required for non-empty)
        $this->validator->validate($property, '', $context);
        $this->assertTrue(true);
    }

    public function test_validate_fails_for_invalid_email_no_at(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'invalid-email', $context);
    }

    public function test_validate_fails_for_invalid_email_no_domain(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'john@', $context);
    }

    public function test_validate_fails_for_invalid_email_no_user(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, '@example.com', $context);
    }

    public function test_validate_fails_for_non_string_value(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 12345, $context);
    }

    public function test_validate_fails_for_array_value(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, ['email@example.com'], $context);
    }

    public function test_violation_contains_correct_property_name(): void
    {
        $property = $this->createPropertyMeta('userEmail', [new Email]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(1, $violations);
            $this->assertSame('userEmail', $violations[0]->getPropertyName());
        }
    }

    public function test_violation_contains_correct_rule_name(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('email', $violations[0]->getRuleName());
        }
    }

    public function test_custom_message_is_used(): void
    {
        $customMessage = 'Please provide a valid email address';
        $property = $this->createPropertyMeta('email', [new Email(message: $customMessage)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame($customMessage, $violations[0]->getMessage());
        }
    }

    public function test_default_message_is_used(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('The value must be a valid email address', $violations[0]->getMessage());
        }
    }

    public function test_violation_contains_invalid_value(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'not-an-email', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('not-an-email', $violations[0]->getInvalidValue());
        }
    }

    /**
     * @param  array<object>  $attributes
     */
    private function createPropertyMeta(string $name, array $attributes = []): PropertyMeta
    {
        return new PropertyMeta(
            name: $name,
            type: TypeDescriptor::mixed(),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: $attributes,
            attributes: $attributes,
        );
    }

    private function createValidationContext(PropertyMeta $property): ValidationContext
    {
        return new ValidationContext(
            property: $property,
            allData: [],
            context: new Context,
        );
    }
}
