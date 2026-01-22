<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\RequiredIf;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\Validators\RequiredIfValidator;

final class RequiredIfValidatorTest extends TestCase
{
    private RequiredIfValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RequiredIfValidator;
    }

    public function test_supports_returns_true_when_required_if_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);

        $this->assertTrue($this->validator->supports($property, 'test@example.com'));
    }

    public function test_supports_returns_false_when_no_required_if_attribute(): void
    {
        $property = $this->createPropertyMeta('email', []);

        $this->assertFalse($this->validator->supports($property, 'test@example.com'));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_when_condition_not_met(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => false]);

        // Email can be null when subscribe is false
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_when_condition_met_and_value_provided(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        $this->validator->validate($property, 'test@example.com', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_when_condition_field_missing(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, []);

        // Condition field not present, so condition is not met
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_with_string_condition_value(): void
    {
        $property = $this->createPropertyMeta('cardNumber', [new RequiredIf('paymentMethod', 'credit_card')]);
        $context = $this->createValidationContext($property, ['paymentMethod' => 'credit_card']);

        $this->validator->validate($property, '4111111111111111', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_when_string_condition_not_met(): void
    {
        $property = $this->createPropertyMeta('cardNumber', [new RequiredIf('paymentMethod', 'credit_card')]);
        $context = $this->createValidationContext($property, ['paymentMethod' => 'paypal']);

        // Card number can be null when payment method is not credit card
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_fails_when_condition_met_and_value_null(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, null, $context);
    }

    public function test_validate_fails_when_condition_met_and_value_empty_string(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, '', $context);
    }

    public function test_validate_fails_when_condition_met_and_value_empty_array(): void
    {
        $property = $this->createPropertyMeta('tags', [new RequiredIf('hasTags', true)]);
        $context = $this->createValidationContext($property, ['hasTags' => true]);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, [], $context);
    }

    public function test_validate_uses_strict_comparison(): void
    {
        // Condition is boolean true
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);

        // String "true" is not the same as boolean true
        $context = $this->createValidationContext($property, ['subscribe' => 'true']);

        // Should pass because 'true' !== true (strict comparison)
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_violation_contains_correct_property_name(): void
    {
        $property = $this->createPropertyMeta('billingEmail', [new RequiredIf('needsInvoice', true)]);
        $context = $this->createValidationContext($property, ['needsInvoice' => true]);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(1, $violations);
            $this->assertSame('billingEmail', $violations[0]->getPropertyName());
        }
    }

    public function test_violation_contains_correct_rule_name(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('required_if', $violations[0]->getRuleName());
        }
    }

    public function test_custom_message_is_used(): void
    {
        $customMessage = 'Email is required when subscribing to newsletter';
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true, message: $customMessage)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame($customMessage, $violations[0]->getMessage());
        }
    }

    public function test_default_message_contains_field_name(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertStringContainsString('subscribe', $violations[0]->getMessage());
        }
    }

    public function test_violation_contains_invalid_value(): void
    {
        $property = $this->createPropertyMeta('email', [new RequiredIf('subscribe', true)]);
        $context = $this->createValidationContext($property, ['subscribe' => true]);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertNull($violations[0]->getInvalidValue());
        }
    }

    public function test_validate_with_integer_condition(): void
    {
        $property = $this->createPropertyMeta('reason', [new RequiredIf('status', 0)]);
        $context = $this->createValidationContext($property, ['status' => 0]);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, null, $context);
    }

    public function test_validate_passes_with_zero_as_valid_value(): void
    {
        $property = $this->createPropertyMeta('discount', [new RequiredIf('hasDiscount', true)]);
        $context = $this->createValidationContext($property, ['hasDiscount' => true]);

        // 0 is a valid non-empty value
        $this->validator->validate($property, 0, $context);
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

    /**
     * @param  array<string, mixed>  $allData
     */
    private function createValidationContext(PropertyMeta $property, array $allData = []): ValidationContext
    {
        return new ValidationContext(
            property: $property,
            allData: $allData,
            context: new Context,
        );
    }
}
