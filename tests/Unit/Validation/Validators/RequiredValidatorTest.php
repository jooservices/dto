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
use JOOservices\Dto\Validation\Validators\RequiredValidator;

final class RequiredValidatorTest extends TestCase
{
    private RequiredValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new RequiredValidator;
    }

    public function test_supports_returns_true_when_required_attribute_present(): void
    {
        $property = $this->createPropertyMeta('name', [new Required]);

        $this->assertTrue($this->validator->supports($property, 'value'));
    }

    public function test_supports_returns_false_when_no_required_attribute(): void
    {
        $property = $this->createPropertyMeta('name', []);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_for_non_null_value(): void
    {
        $property = $this->createPropertyMeta('name', [new Required]);
        $context = $this->createValidationContext($property);

        // Should not throw
        $this->validator->validate($property, 'John Doe', $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_zero(): void
    {
        $property = $this->createPropertyMeta('count', [new Required]);
        $context = $this->createValidationContext($property);

        // 0 is a valid value, not empty
        $this->validator->validate($property, 0, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_false(): void
    {
        $property = $this->createPropertyMeta('active', [new Required]);
        $context = $this->createValidationContext($property);

        // false is a valid value, not empty
        $this->validator->validate($property, false, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_non_empty_array(): void
    {
        $property = $this->createPropertyMeta('items', [new Required]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, ['item1'], $context);
        $this->assertTrue(true);
    }

    public function test_validate_fails_for_null_value(): void
    {
        $property = $this->createPropertyMeta('name', [new Required]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, null, $context);
    }

    public function test_validate_fails_for_empty_string(): void
    {
        $property = $this->createPropertyMeta('name', [new Required]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, '', $context);
    }

    public function test_validate_fails_for_empty_array(): void
    {
        $property = $this->createPropertyMeta('items', [new Required]);
        $context = $this->createValidationContext($property);

        $this->expectException(ValidationException::class);
        $this->validator->validate($property, [], $context);
    }

    public function test_violation_contains_correct_property_name(): void
    {
        $property = $this->createPropertyMeta('username', [new Required]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertCount(1, $violations);
            $this->assertSame('username', $violations[0]->getPropertyName());
        }
    }

    public function test_violation_contains_correct_rule_name(): void
    {
        $property = $this->createPropertyMeta('email', [new Required]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('required', $violations[0]->getRuleName());
        }
    }

    public function test_custom_message_is_used(): void
    {
        $customMessage = 'Email is mandatory';
        $property = $this->createPropertyMeta('email', [new Required(message: $customMessage)]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame($customMessage, $violations[0]->getMessage());
        }
    }

    public function test_default_message_is_used(): void
    {
        $property = $this->createPropertyMeta('name', [new Required]);
        $context = $this->createValidationContext($property);

        try {
            $this->validator->validate($property, null, $context);
            $this->fail('Expected ValidationException');
        } catch (ValidationException $e) {
            $violations = $e->getViolations();
            $this->assertSame('This field is required', $violations[0]->getMessage());
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
