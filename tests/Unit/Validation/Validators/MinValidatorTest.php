<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Min;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\Validators\MinValidator;

final class MinValidatorTest extends TestCase
{
    private MinValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new MinValidator;
    }

    public function test_supports_returns_true_when_min_attribute_present(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);

        $this->assertTrue($this->validator->supports($property, 25));
    }

    public function test_supports_returns_false_when_no_min_attribute(): void
    {
        $property = $this->createPropertyMeta('age', []);

        $this->assertFalse($this->validator->supports($property, 25));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_for_value_above_min(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 25, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_value_equal_to_min(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 18, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_float_above_min(): void
    {
        $property = $this->createPropertyMeta('price', [new Min(0.01)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, 9.99, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_string_numeric_above_min(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, '25', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_null_value(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        // Null is handled by RequiredValidator
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_fails_for_value_below_min(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 15, $context);
    }

    public function test_validate_fails_for_float_below_min(): void
    {
        $property = $this->createPropertyMeta('price', [new Min(0.01)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 0.001, $context);
    }

    public function test_validate_fails_for_negative_value_below_min(): void
    {
        $property = $this->createPropertyMeta('balance', [new Min(0)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, -5, $context);
    }

    public function test_validate_fails_for_non_numeric_string(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, 'not-a-number', $context);
    }

    public function test_validate_fails_for_array_value(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, [25], $context);
    }

    public function test_violation_contains_correct_property_name(): void
    {
        $property = $this->createPropertyMeta('userAge', [new Min(18)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 10, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(1, $violations);
            $this->assertSame('userAge', $violations[0]->getPropertyName());
        }
    }

    public function test_violation_contains_correct_rule_name(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 10, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('min', $violations[0]->getRuleName());
        }
    }

    public function test_custom_message_is_used(): void
    {
        $customMessage = 'You must be at least 18 years old';
        $property = $this->createPropertyMeta('age', [new Min(18, message: $customMessage)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 15, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame($customMessage, $violations[0]->getMessage());
        }
    }

    public function test_default_message_contains_min_value(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 10, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertStringContainsString('18', $violations[0]->getMessage());
        }
    }

    public function test_violation_contains_invalid_value(): void
    {
        $property = $this->createPropertyMeta('age', [new Min(18)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, 10, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame(10, $violations[0]->getInvalidValue());
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
