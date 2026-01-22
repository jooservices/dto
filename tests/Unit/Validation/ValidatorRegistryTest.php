<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorInterface;
use JOOservices\Dto\Validation\ValidatorRegistry;

final class ValidatorRegistryTest extends TestCase
{
    private ValidatorRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new ValidatorRegistry;
    }

    public function test_register_adds_validator(): void
    {
        $validator = $this->createMockValidator(supports: true);

        $this->registry->register($validator);

        $this->assertTrue($this->registry->canValidate(
            $this->createPropertyMeta('test'),
            'value',
        ));
    }

    public function test_get_returns_first_supporting_validator(): void
    {
        $validator1 = $this->createMockValidator(supports: false);
        $validator2 = $this->createMockValidator(supports: true);
        $validator3 = $this->createMockValidator(supports: true);

        $this->registry->register($validator1);
        $this->registry->register($validator2);
        $this->registry->register($validator3);

        $result = $this->registry->get($this->createPropertyMeta('test'), 'value');

        $this->assertSame($validator2, $result);
    }

    public function test_get_returns_null_when_no_validator_supports(): void
    {
        $validator = $this->createMockValidator(supports: false);

        $this->registry->register($validator);

        $result = $this->registry->get($this->createPropertyMeta('test'), 'value');

        $this->assertNull($result);
    }

    public function test_can_validate_returns_true_when_validator_supports(): void
    {
        $validator = $this->createMockValidator(supports: true);

        $this->registry->register($validator);

        $this->assertTrue($this->registry->canValidate(
            $this->createPropertyMeta('test'),
            'value',
        ));
    }

    public function test_can_validate_returns_false_when_no_validator_supports(): void
    {
        $validator = $this->createMockValidator(supports: false);

        $this->registry->register($validator);

        $this->assertFalse($this->registry->canValidate(
            $this->createPropertyMeta('test'),
            'value',
        ));
    }

    public function test_validate_collects_all_violations(): void
    {
        $property = $this->createPropertyMeta('email', [new Required, new Email]);

        // Create validators that throw ValidationException
        $requiredValidator = new class implements ValidatorInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
            {
                throw ValidationException::fromViolations('Required failed', [
                    new RuleViolation('email', 'required', 'Field is required'),
                ]);
            }
        };

        $emailValidator = new class implements ValidatorInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
            {
                throw ValidationException::fromViolations('Email failed', [
                    new RuleViolation('email', 'email', 'Invalid email'),
                ]);
            }
        };

        $this->registry->register($requiredValidator, 100);
        $this->registry->register($emailValidator, 10);

        $context = $this->createValidationContext($property, []);

        $this->expectException(ValidationException::class);

        try {
            $this->registry->validate($property, '', $context);
        } catch (ValidationException $e) {
            $this->assertCount(2, $e->getViolations());

            throw $e;
        }
    }

    public function test_validate_passes_when_all_validators_pass(): void
    {
        $property = $this->createPropertyMeta('email');

        $validator = new class implements ValidatorInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
            {
                // Do nothing
            }
        };

        $this->registry->register($validator);

        $context = $this->createValidationContext($property, ['email' => 'test@example.com']);

        // Should not throw
        $this->registry->validate($property, 'test@example.com', $context);
        $this->assertTrue(true); // Assertion to confirm no exception
    }

    public function test_priority_sorting_works_correctly(): void
    {
        $lowPriority = $this->createMockValidator(supports: true);
        $highPriority = $this->createMockValidator(supports: true);

        $this->registry->register($lowPriority, 10);
        $this->registry->register($highPriority, 100);

        $result = $this->registry->get($this->createPropertyMeta('test'), 'value');

        // Higher priority should be returned first
        $this->assertSame($highPriority, $result);
    }

    // ... [skip simple tests] ...

    private function createMockValidator(bool $supports): ValidatorInterface
    {
        return new class($supports) implements ValidatorInterface
        {
            public function __construct(private bool $canSupport) {}

            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return $this->canSupport;
            }

            public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
            {
                // no-op
            }
        };
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
    private function createValidationContext(PropertyMeta $property, array $allData): ValidationContext
    {
        return new ValidationContext(
            property: $property,
            allData: $allData,
            context: new Context,
        );
    }
}
