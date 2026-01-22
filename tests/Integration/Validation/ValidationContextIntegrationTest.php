<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;

/**
 * Tests for ValidationContext usage in validators.
 *
 * These tests verify that ValidationContext properly provides access
 * to all input data for conditional validation scenarios.
 */
final class ValidationContextIntegrationTest extends TestCase
{
    public function test_validation_context_receives_all_input_data(): void
    {
        $allData = [
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 25,
            'country' => 'US',
        ];

        $context = new ValidationContext(
            property: $this->createPropertyMeta('email'),
            allData: $allData,
            context: new Context,
        );

        // Should have access to all fields
        $this->assertTrue($context->hasField('name'));
        $this->assertTrue($context->hasField('email'));
        $this->assertTrue($context->hasField('age'));
        $this->assertTrue($context->hasField('country'));

        // Should return correct values
        $this->assertSame('John', $context->getFieldValue('name'));
        $this->assertSame('john@example.com', $context->getFieldValue('email'));
        $this->assertSame(25, $context->getFieldValue('age'));
        $this->assertSame('US', $context->getFieldValue('country'));
    }

    public function test_validation_context_with_nested_data(): void
    {
        $allData = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
            'settings' => [
                'notifications' => true,
            ],
        ];

        $context = new ValidationContext(
            property: $this->createPropertyMeta('user'),
            allData: $allData,
            context: new Context,
        );

        $user = $context->getFieldValue('user');
        $this->assertIsArray($user);
        $this->assertSame('John', $user['name']);

        $settings = $context->getFieldValue('settings');
        $this->assertIsArray($settings);
        $this->assertTrue($settings['notifications']);
    }

    public function test_validation_context_preserves_main_context(): void
    {
        $mainContext = new Context(validationEnabled: true);
        $mainContext = $mainContext->withCustomData(['key' => 'value']);

        $validationContext = new ValidationContext(
            property: $this->createPropertyMeta('test'),
            allData: [],
            context: $mainContext,
        );

        $this->assertTrue($validationContext->context->validationEnabled);
        $this->assertSame('value', $validationContext->context->getCustom('key'));
    }

    public function test_validation_context_with_empty_data(): void
    {
        $context = new ValidationContext(
            property: $this->createPropertyMeta('test'),
            allData: [],
            context: new Context,
        );

        $this->assertFalse($context->hasField('anyField'));
        $this->assertNull($context->getFieldValue('anyField'));
    }

    public function test_dto_from_with_validation_context_flow(): void
    {
        // This tests that the full flow from Dto::from() to validation
        // properly creates and uses ValidationContext
        $context = new Context(validationEnabled: true);

        $dto = TestContextDto::from([
            'name' => 'John',
            'email' => 'john@example.com',
        ], $context);

        $this->assertSame('John', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
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

class TestContextDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly string $email,
    ) {}
}
