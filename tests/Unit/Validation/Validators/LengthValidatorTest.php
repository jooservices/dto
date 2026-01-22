<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Length;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\Validators\LengthValidator;

final class LengthValidatorTest extends TestCase
{
    private LengthValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new LengthValidator;
    }

    public function test_supports_returns_true_when_length_attribute_present(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);

        $this->assertTrue($this->validator->supports($property, 'john'));
    }

    public function test_supports_returns_false_when_no_length_attribute(): void
    {
        $property = $this->createPropertyMeta('username', []);

        $this->assertFalse($this->validator->supports($property, 'john'));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_for_value_within_length(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'johndoe', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_value_at_min_length(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'joe', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_value_at_max_length(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, str_repeat('a', 20), $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_with_only_min(): void
    {
        $property = $this->createPropertyMeta('password', [new Length(min: 8)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'verylongpassword', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_with_only_max(): void
    {
        $property = $this->createPropertyMeta('title', [new Length(max: 100)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 'Short title', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_null_value(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        // Null is handled by RequiredValidator
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_multibyte_string(): void
    {
        $property = $this->createPropertyMeta('name', [new Length(min: 2, max: 10)]);
        $context = $this->createValidationContext($property);

        // 3 characters (Japanese)
        $this->validator->validate($property, '日本語', $context);
        $this->assertTrue(true);
    }

    public function test_validate_fails_for_value_below_min_length(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'ab', $context);
    }

    public function test_validate_fails_for_value_above_max_length(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, str_repeat('a', 21), $context);
    }

    public function test_validate_fails_for_password_too_short(): void
    {
        $property = $this->createPropertyMeta('password', [new Length(min: 8)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'short', $context);
    }

    public function test_validate_fails_for_title_too_long(): void
    {
        $property = $this->createPropertyMeta('title', [new Length(max: 100)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, str_repeat('a', 101), $context);
    }

    public function test_validate_fails_for_non_string_value(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 12345, $context);
    }

    public function test_validate_fails_for_array_value(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, ['john'], $context);
    }

    public function test_violation_contains_correct_property_name(): void
    {
        $property = $this->createPropertyMeta('displayName', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'ab', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(1, $violations);
            $this->assertSame('displayName', $violations[0]->getPropertyName());
        }
    }

    public function test_violation_contains_correct_rule_name(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'ab', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('length', $violations[0]->getRuleName());
        }
    }

    public function test_custom_message_is_used(): void
    {
        $customMessage = 'Username must be between 3 and 20 characters';
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20, message: $customMessage)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'ab', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame($customMessage, $violations[0]->getMessage());
        }
    }

    public function test_default_message_for_min_and_max(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'ab', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $message = $violations[0]->getMessage();
            $this->assertStringContainsString('3', $message);
            $this->assertStringContainsString('20', $message);
        }
    }

    public function test_default_message_for_min_only(): void
    {
        $property = $this->createPropertyMeta('password', [new Length(min: 8)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'short', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $message = $violations[0]->getMessage();
            $this->assertStringContainsString('8', $message);
            $this->assertStringContainsString('at least', $message);
        }
    }

    public function test_default_message_for_max_only(): void
    {
        $property = $this->createPropertyMeta('title', [new Length(max: 100)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, str_repeat('a', 101), $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $message = $violations[0]->getMessage();
            $this->assertStringContainsString('100', $message);
            $this->assertStringContainsString('at most', $message);
        }
    }

    public function test_violation_contains_invalid_value(): void
    {
        $property = $this->createPropertyMeta('username', [new Length(min: 3, max: 20)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 'ab', $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('ab', $violations[0]->getInvalidValue());
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
