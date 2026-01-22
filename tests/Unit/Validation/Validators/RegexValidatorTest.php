<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\Validators\RegexValidator;

final class RegexValidatorTest extends TestCase
{
    private RegexValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RegexValidator;
    }

    public function test_supports_returns_true_when_regex_attribute_present(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);

        $this->assertTrue($this->validator->supports($property, 'US-1234'));
    }

    public function test_supports_returns_false_when_no_regex_attribute(): void
    {
        $property = $this->createPropertyMeta('code', []);

        $this->assertFalse($this->validator->supports($property, 'US-1234'));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_for_matching_pattern(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'US-1234', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_alphanumeric_pattern(): void
    {
        $property = $this->createPropertyMeta('username', [new Regex('/^[a-z0-9_]+$/')]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'john_doe123', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_phone_pattern(): void
    {
        $property = $this->createPropertyMeta('phone', [new Regex('/^\+?[1-9]\d{1,14}$/')]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, '+12025551234', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_null_value(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        // Null is handled by RequiredValidator
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_empty_string(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        // Empty string is valid (use Required for non-empty)
        $this->validator->validate($property, '', $context);
        $this->assertTrue(true);
    }

    public function test_validate_fails_for_non_matching_pattern(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'invalid-code', $context);
    }

    public function test_validate_fails_for_partial_match(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'US-123', $context); // Missing one digit
    }

    public function test_validate_fails_for_wrong_case(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'us-1234', $context); // Lowercase letters
    }

    public function test_validate_fails_for_non_string_value(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 12345, $context);
    }

    public function test_validate_fails_for_array_value(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, ['US-1234'], $context);
    }

    public function test_violation_contains_correct_property_name(): void
    {
        $property = $this->createPropertyMeta('productCode', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(1, $violations);
            $this->assertSame('productCode', $violations[0]->getPropertyName());
        }
    }

    public function test_violation_contains_correct_rule_name(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('regex', $violations[0]->getRuleName());
        }
    }

    public function test_custom_message_is_used(): void
    {
        $customMessage = 'Code must be in format XX-0000';
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/', message: $customMessage)]);
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
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'invalid', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('The value does not match the required pattern', $violations[0]->getMessage());
        }
    }

    public function test_violation_contains_invalid_value(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[A-Z]{2}-\d{4}$/')]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'bad-code', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('bad-code', $violations[0]->getInvalidValue());
        }
    }

    public function test_validate_with_case_insensitive_pattern(): void
    {
        $property = $this->createPropertyMeta('code', [new Regex('/^[a-z]+$/i')]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'ABC', $context);
        $this->assertTrue(true);
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
