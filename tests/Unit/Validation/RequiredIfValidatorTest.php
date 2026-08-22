<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRequiredIfDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class RequiredIfValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testOtherFieldNotMatchingSkipsValidation(): void
    {
        $dto = ValidationFixtureRequiredIfDto::fromArray(
            ['active' => false, 'reason' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertFalse($dto->active);
        self::assertNull($dto->reason);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testOtherFieldMatchingWithValuePresentPasses(): void
    {
        $dto = ValidationFixtureRequiredIfDto::fromArray(
            ['active' => true, 'reason' => 'because'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertTrue($dto->active);
        self::assertSame('because', $dto->reason);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testOtherFieldMatchingWithoutValueFailsValidation(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureRequiredIfDto::fromArray(
            ['active' => true, 'reason' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * C14 regression: the comparison against `RequiredIf::$value` must use the
     * *post-cast* value of the other field, not the raw hydration input. The
     * "active" field is typed `bool`, so a raw string '1' is cast to `true`
     * before RequiredIfValidator ever sees it — the rule must still fire.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testComparisonUsesCastBooleanNotRawString(): void
    {
        $this->expectException(ValidationException::class);
        ValidationFixtureRequiredIfDto::fromArray(
            ['active' => '1', 'reason' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }

    /**
     * Symmetric case: a raw string '0' casts to `false`, which does not match
     * the rule's `true` — validation must be skipped.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testComparisonUsesCastBooleanFalseSkipsValidation(): void
    {
        $dto = ValidationFixtureRequiredIfDto::fromArray(
            ['active' => '0', 'reason' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertFalse($dto->active);
        self::assertNull($dto->reason);
    }
}
