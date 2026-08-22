<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use Closure;
use JOOservices\Dto\Meta\ReflectionTypeDescriptorReader;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\CountableLabel;
use JOOservices\Dto\Tests\Fixtures\CountableValue;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

final class ReflectionTypeDescriptorReaderTest extends TestCase
{
    /**
     * Generic test helper: intentionally accepts closures of varying parameter
     * types (string, int|string, an intersection type, …) to build a
     * ReflectionParameter for each case under test.
     *
     * @throws ReflectionException
     *
     * @phpstan-ignore missingType.callable
     */
    private function firstParameter(Closure $closure): ReflectionParameter
    {
        return (new ReflectionFunction($closure))->getParameters()[0];
    }

    public function testNullTypeDescribesAsMixed(): void
    {
        $descriptor = (new ReflectionTypeDescriptorReader())->describe(null);

        self::assertSame(TypeDescriptor::KIND_MIXED, $descriptor->kind);
        self::assertTrue($descriptor->allowsNull());
    }

    /**
     * @throws ReflectionException
     */
    public function testUntypedParameterDescribesAsMixed(): void
    {
        $parameter = $this->firstParameter(static function ($value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_MIXED, $descriptor->kind);
    }

    /**
     * Regression: `ReflectionNamedType::isBuiltin()` returns true for a
     * literal `mixed` type-hint, so a naive builtin check would describe it
     * as `KIND_BUILTIN` with `builtin: 'mixed'` — and no caster supports that,
     * so a property literally typed `mixed` could never hydrate through
     * `fromArray()`. It must describe as `KIND_MIXED`.
     *
     * @throws ReflectionException
     */
    public function testLiteralMixedTypeHintDescribesAsMixedKind(): void
    {
        $parameter = $this->firstParameter(static function (mixed $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_MIXED, $descriptor->kind);
        self::assertTrue($descriptor->allowsNull());
    }

    /**
     * @throws ReflectionException
     */
    public function testBuiltinNonNullableType(): void
    {
        $parameter = $this->firstParameter(static function (string $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_BUILTIN, $descriptor->kind);
        self::assertSame('string', $descriptor->builtin);
        self::assertFalse($descriptor->allowsNull());
    }

    /**
     * @throws ReflectionException
     */
    public function testNullableBuiltinType(): void
    {
        $parameter = $this->firstParameter(static function (?int $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_BUILTIN, $descriptor->kind);
        self::assertTrue($descriptor->allowsNull());
    }

    /**
     * @throws ReflectionException
     */
    public function testEnumTypeIsDescribedAsEnumKind(): void
    {
        $parameter = $this->firstParameter(static function (Status $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_ENUM, $descriptor->kind);
        self::assertSame(Status::class, $descriptor->className);
    }

    /**
     * @throws ReflectionException
     */
    public function testExistingClassTypeIsDescribedAsClassKind(): void
    {
        $parameter = $this->firstParameter(static function (UserDto $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_CLASS, $descriptor->kind);
        self::assertSame(UserDto::class, $descriptor->className);
    }

    /**
     * A named type pointing at a class/interface that does not actually exist
     * must pass through as `mixed` rather than blow up meta building.
     *
     * @throws ReflectionException
     */
    public function testUnresolvableClassNameFallsBackToMixed(): void
    {
        /** @phpstan-ignore class.notFound */
        $parameter = $this->firstParameter(static function (TotallyMadeUpTypeForTesting $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_MIXED, $descriptor->kind);
    }

    /**
     * PHP's own Reflection API does not preserve source declaration order for
     * union type members (verified: `int|string` and `string|int` both report
     * the same internal order) — so this only asserts that every declared
     * member is described, not a specific index.
     *
     * @throws ReflectionException
     */
    public function testUnionTypeDescribesEachMember(): void
    {
        $parameter = $this->firstParameter(static function (int | string $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_UNION, $descriptor->kind);
        self::assertCount(2, $descriptor->members);
        $builtins = array_map(static fn(TypeDescriptor $member): ?string => $member->builtin, $descriptor->members);
        self::assertEqualsCanonicalizing(['int', 'string'], $builtins);
        self::assertFalse($descriptor->allowsNull());
    }

    /**
     * @throws ReflectionException
     */
    public function testNullableUnionTypeAllowsNull(): void
    {
        $parameter = $this->firstParameter(static function (int | string | null $value): void {
            unset($value);
        });

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_UNION, $descriptor->kind);
        self::assertTrue($descriptor->allowsNull());
    }

    /**
     * @throws ReflectionException
     */
    public function testIntersectionTypeDescribesEachMemberAndIsNeverNullable(): void
    {
        $parameter = $this->firstParameter(
            static function (CountableLabel & CountableValue $value): void {
                unset($value);
            },
        );

        $descriptor = (new ReflectionTypeDescriptorReader())->describe($parameter->getType());

        self::assertSame(TypeDescriptor::KIND_INTERSECTION, $descriptor->kind);
        self::assertCount(2, $descriptor->members);
        self::assertSame(CountableLabel::class, $descriptor->members[0]->className);
        self::assertSame(CountableValue::class, $descriptor->members[1]->className);
        self::assertFalse($descriptor->allowsNull());
    }
}
