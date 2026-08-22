<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureInnerDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureValidDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureValidListDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureValidScalarDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class ValidValidatorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValidNestedDtoPasses(): void
    {
        $inner = ValidationFixtureInnerDto::fromArray(
            ['name' => 'ok'],
            Context::loose(),
        );
        $outer = ValidationFixtureValidDto::fromArray(
            ['inner' => $inner],
            Context::loose(),
        );

        $outer->validate();

        self::assertSame('ok', $outer->inner->name);
    }

    /**
     * The nested DTO was built with validation disabled (so its own
     * construction did not check #[Required]) — #[Valid] on the parent
     * property must force a real re-validation, not merely check "is it a
     * DTO instance".
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidNestedDtoFailsValidation(): void
    {
        $inner = ValidationFixtureInnerDto::fromArray(
            ['name' => null],
            Context::loose(),
        );
        $outer = ValidationFixtureValidDto::fromArray(
            ['inner' => $inner],
            Context::loose(),
        );

        $this->expectException(ValidationException::class);
        $outer->validate();
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayOfValidNestedDtosPasses(): void
    {
        $items = [
            ValidationFixtureInnerDto::fromArray(['name' => 'a'], Context::loose()),
            ValidationFixtureInnerDto::fromArray(['name' => 'b'], Context::loose()),
        ];
        $outer = ValidationFixtureValidListDto::fromArray(
            ['items' => $items],
            Context::loose(),
        );

        $outer->validate();

        self::assertCount(2, $outer->items);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayWithOneInvalidNestedDtoFailsValidation(): void
    {
        $items = [
            ValidationFixtureInnerDto::fromArray(['name' => 'a'], Context::loose()),
            ValidationFixtureInnerDto::fromArray(['name' => null], Context::loose()),
        ];
        $outer = ValidationFixtureValidListDto::fromArray(
            ['items' => $items],
            Context::loose(),
        );

        $this->expectException(ValidationException::class);
        $outer->validate();
    }

    /**
     * Non-DTO array items are simply not this rule's concern.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayOfNonDtoItemsPasses(): void
    {
        $outer = ValidationFixtureValidListDto::fromArray(
            ['items' => ['a', 'b']],
            Context::loose(),
        );

        $outer->validate();

        self::assertSame(['a', 'b'], $outer->items);
    }

    /**
     * A non-array, non-DTO value under #[Valid] is out of scope for this
     * rule and must not raise.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testScalarValuePasses(): void
    {
        $dto = ValidationFixtureValidScalarDto::fromArray(
            ['note' => 'hello'],
            Context::loose(),
        );

        $dto->validate();

        self::assertSame('hello', $dto->note);
    }
}
