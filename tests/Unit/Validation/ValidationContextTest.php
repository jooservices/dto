<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;

final class ValidationContextTest extends TestCase
{
    public function test_constructor_sets_all_properties(): void
    {
        $property = $this->createPropertyMeta('testField');
        $allData = ['testField' => 'value', 'otherField' => 123];
        $context = new Context;

        $validationContext = new ValidationContext($property, $allData, $context);

        $this->assertSame($property, $validationContext->property);
        $this->assertSame($allData, $validationContext->allData);
        $this->assertSame($context, $validationContext->context);
    }

    public function test_has_field_returns_true_for_existing_field(): void
    {
        $validationContext = $this->createValidationContext(['name' => 'John', 'age' => 25]);

        $this->assertTrue($validationContext->hasField('name'));
        $this->assertTrue($validationContext->hasField('age'));
    }

    public function test_has_field_returns_false_for_missing_field(): void
    {
        $validationContext = $this->createValidationContext(['name' => 'John']);

        $this->assertFalse($validationContext->hasField('email'));
        $this->assertFalse($validationContext->hasField('age'));
    }

    public function test_has_field_returns_true_for_null_value(): void
    {
        $validationContext = $this->createValidationContext(['name' => null]);

        $this->assertTrue($validationContext->hasField('name'));
    }

    public function test_get_field_value_returns_value_for_existing_field(): void
    {
        $validationContext = $this->createValidationContext([
            'name' => 'John',
            'age' => 25,
            'active' => true,
        ]);

        $this->assertSame('John', $validationContext->getFieldValue('name'));
        $this->assertSame(25, $validationContext->getFieldValue('age'));
        $this->assertTrue($validationContext->getFieldValue('active'));
    }

    public function test_get_field_value_returns_null_for_missing_field(): void
    {
        $validationContext = $this->createValidationContext(['name' => 'John']);

        $this->assertNull($validationContext->getFieldValue('email'));
        $this->assertNull($validationContext->getFieldValue('nonexistent'));
    }

    public function test_get_field_value_returns_null_for_field_with_null_value(): void
    {
        $validationContext = $this->createValidationContext(['name' => null]);

        $this->assertNull($validationContext->getFieldValue('name'));
    }

    /**
     * @param  array<string, mixed>  $allData
     */
    private function createValidationContext(array $allData): ValidationContext
    {
        return new ValidationContext(
            property: $this->createPropertyMeta('testField'),
            allData: $allData,
            context: new Context,
        );
    }

    private function createPropertyMeta(string $name): PropertyMeta
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
            validationRules: [],
            attributes: [],
        );
    }
}
