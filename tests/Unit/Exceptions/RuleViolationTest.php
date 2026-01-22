<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Tests\TestCase;

final class RuleViolationTest extends TestCase
{
    public function test_construct(): void
    {
        $violation = new RuleViolation(
            propertyName: 'age',
            ruleName: 'min',
            message: 'Age must be at least 18',
        );

        $this->assertInstanceOf(RuleViolation::class, $violation);
    }

    public function test_get_property_name(): void
    {
        $violation = new RuleViolation('email', 'required', 'Email is required');

        $this->assertSame('email', $violation->getPropertyName());
    }

    public function test_get_rule_name(): void
    {
        $violation = new RuleViolation('age', 'min', 'Too young');

        $this->assertSame('min', $violation->getRuleName());
    }

    public function test_get_message(): void
    {
        $message = 'Password must be at least 8 characters';
        $violation = new RuleViolation('password', 'minLength', $message);

        $this->assertSame($message, $violation->getMessage());
    }

    public function test_get_invalid_value(): void
    {
        $violation = new RuleViolation(
            propertyName: 'age',
            ruleName: 'min',
            message: 'Too young',
            invalidValue: 15,
        );

        $this->assertSame(15, $violation->getInvalidValue());
    }

    public function test_get_invalid_value_defaults_to_null(): void
    {
        $violation = new RuleViolation('name', 'required', 'Required');

        $this->assertNull($violation->getInvalidValue());
    }

    public function test_get_parameters(): void
    {
        $parameters = ['min' => 18, 'max' => 99];
        $violation = new RuleViolation(
            propertyName: 'age',
            ruleName: 'range',
            message: 'Age out of range',
            invalidValue: 150,
            parameters: $parameters,
        );

        $this->assertSame($parameters, $violation->getParameters());
    }

    public function test_get_parameters_defaults_to_empty_array(): void
    {
        $violation = new RuleViolation('name', 'required', 'Required');

        $this->assertSame([], $violation->getParameters());
    }

    public function test_get_parameter(): void
    {
        $violation = new RuleViolation(
            propertyName: 'password',
            ruleName: 'minLength',
            message: 'Too short',
            parameters: ['min' => 8, 'max' => 32],
        );

        $this->assertSame(8, $violation->getParameter('min'));
        $this->assertSame(32, $violation->getParameter('max'));
    }

    public function test_get_parameter_with_default(): void
    {
        $violation = new RuleViolation(
            propertyName: 'name',
            ruleName: 'required',
            message: 'Required',
            parameters: [],
        );

        $this->assertSame('default', $violation->getParameter('nonexistent', 'default'));
        $this->assertNull($violation->getParameter('nonexistent'));
    }

    public function test_get_formatted_message(): void
    {
        $violation = new RuleViolation(
            propertyName: 'email',
            ruleName: 'email',
            message: 'Invalid email format',
        );

        $formatted = $violation->getFormattedMessage();

        $this->assertStringContainsString('email', $formatted);
        $this->assertStringContainsString('email', $formatted);
        $this->assertStringContainsString('Invalid email format', $formatted);
    }

    public function test_get_formatted_message_format(): void
    {
        $violation = new RuleViolation('age', 'min', 'Too young');

        $formatted = $violation->getFormattedMessage();

        $this->assertStringStartsWith('[age]', $formatted);
        $this->assertStringContainsString('min:', $formatted);
        $this->assertStringContainsString('Too young', $formatted);
    }

    public function test_readonly_properties(): void
    {
        $violation = new RuleViolation(
            propertyName: 'name',
            ruleName: 'required',
            message: 'Required',
        );

        $this->assertSame('name', $violation->getPropertyName());
        $this->assertSame('required', $violation->getRuleName());
        $this->assertSame('Required', $violation->getMessage());
    }

    public function test_with_complex_invalid_value(): void
    {
        $invalidValue = ['nested' => ['array' => 'value']];
        $violation = new RuleViolation(
            propertyName: 'data',
            ruleName: 'valid',
            message: 'Invalid data',
            invalidValue: $invalidValue,
        );

        $this->assertSame($invalidValue, $violation->getInvalidValue());
    }

    public function test_with_null_invalid_value(): void
    {
        $violation = new RuleViolation(
            propertyName: 'optional',
            ruleName: 'required',
            message: 'Required',
            invalidValue: null,
        );

        $this->assertNull($violation->getInvalidValue());
    }

    public function test_parameters_preserves_types(): void
    {
        $parameters = [
            'string' => 'value',
            'int' => 42,
            'float' => 3.14,
            'bool' => true,
            'array' => [1, 2, 3],
        ];

        $violation = new RuleViolation(
            propertyName: 'test',
            ruleName: 'complex',
            message: 'Test',
            parameters: $parameters,
        );

        $result = $violation->getParameters();

        $this->assertSame('value', $result['string']);
        $this->assertSame(42, $result['int']);
        $this->assertSame(3.14, $result['float']);
        $this->assertTrue($result['bool']);
        $this->assertSame([1, 2, 3], $result['array']);
    }
}
