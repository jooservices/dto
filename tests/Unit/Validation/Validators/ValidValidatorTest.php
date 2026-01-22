<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation\Validators;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Valid;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\Validators\ValidValidator;

final class ValidValidatorTest extends TestCase
{
    private ValidValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new ValidValidator;
    }

    public function test_supports_returns_true_when_valid_attribute_present(): void
    {
        $property = $this->createPropertyMeta('address', [new Valid]);

        $this->assertTrue($this->validator->supports($property, new TestAddressDto('123 Main St', 'City')));
    }

    public function test_supports_returns_false_when_no_valid_attribute(): void
    {
        $property = $this->createPropertyMeta('address', []);

        $this->assertFalse($this->validator->supports($property, new TestAddressDto('123 Main St', 'City')));
    }

    public function test_supports_returns_false_when_other_attribute_present(): void
    {
        $property = $this->createPropertyMeta('email', [new Email]);

        $this->assertFalse($this->validator->supports($property, 'value'));
    }

    public function test_validate_passes_for_null_value(): void
    {
        $property = $this->createPropertyMeta('address', [new Valid]);
        $context = $this->createValidationContext($property);

        // Null is handled by RequiredValidator
        $this->validator->validate($property, null, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_valid_dto(): void
    {
        $property = $this->createPropertyMeta('address', [new Valid]);
        $context = $this->createValidationContext($property);

        $dto = new TestAddressDto('123 Main St', 'New York');
        $this->validator->validate($property, $dto, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_array_of_dtos_with_each_item(): void
    {
        $property = $this->createPropertyMeta('addresses', [new Valid(eachItem: true)]);
        $context = $this->createValidationContext($property);

        $dtos = [
            new TestAddressDto('123 Main St', 'New York'),
            new TestAddressDto('456 Oak Ave', 'Los Angeles'),
        ];

        $this->validator->validate($property, $dtos, $context);
        $this->assertTrue(true);
    }

    public function test_validate_passes_for_empty_array_with_each_item(): void
    {
        $property = $this->createPropertyMeta('addresses', [new Valid(eachItem: true)]);
        $context = $this->createValidationContext($property);

        $this->validator->validate($property, [], $context);
        $this->assertTrue(true);
    }

    public function test_validate_ignores_non_dto_values(): void
    {
        $property = $this->createPropertyMeta('data', [new Valid]);
        $context = $this->createValidationContext($property);

        // Non-DTO values are ignored (no exception thrown)
        $this->validator->validate($property, 'not a dto', $context);
        $this->assertTrue(true);
    }

    public function test_validate_ignores_non_dto_items_in_array(): void
    {
        $property = $this->createPropertyMeta('items', [new Valid(eachItem: true)]);
        $context = $this->createValidationContext($property);

        // Non-DTO items in array are ignored
        $this->validator->validate($property, ['string', 123, null], $context);
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

class TestAddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
    ) {}
}
