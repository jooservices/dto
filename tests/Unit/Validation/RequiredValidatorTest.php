<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRequiredDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class RequiredValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPresentValuePasses(): void
    {
        $dto = ValidationFixtureRequiredDto::fromArray(
            ['value' => 'present'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertSame('present', $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNullValueFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureRequiredDto::fromArray(
            ['value' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testEmptyStringFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureRequiredDto::fromArray(
            ['value' => ''],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     */
    public function testViolationCarriesRuleNameAndMessage(): void
    {
        try {
            ValidationFixtureRequiredDto::fromArray(
                ['value' => null],
                new Context(validationEnabled: true, sourceKeyOut: false),
            );
            self::fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $violation = $exception->getViolations()[0];
            self::assertSame('value', $violation->property);
            self::assertSame('required', $violation->rule);
            self::assertSame('This field is required.', $violation->message);
            self::assertNull($violation->value);
        }
    }
}
