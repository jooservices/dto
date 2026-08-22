<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;

final class TypeDescriptorTest extends TestCase
{
    public function testAllowsNullReflectsNullabilityFlag(): void
    {
        $nullable = new TypeDescriptor(kind: TypeDescriptor::KIND_MIXED, nullability: TypeDescriptor::NULLABLE);
        $required = new TypeDescriptor(kind: TypeDescriptor::KIND_BUILTIN, nullability: TypeDescriptor::REQUIRED);

        self::assertTrue($nullable->allowsNull());
        self::assertFalse($required->allowsNull());
    }

    public function testMixedFactoryProducesNullableMixedDescriptor(): void
    {
        $descriptor = TypeDescriptor::mixed();

        self::assertSame(TypeDescriptor::KIND_MIXED, $descriptor->kind);
        self::assertTrue($descriptor->allowsNull());
    }

    public function testBuiltinFactoryProducesRequiredBuiltinDescriptor(): void
    {
        $descriptor = TypeDescriptor::builtin('int');

        self::assertSame(TypeDescriptor::KIND_BUILTIN, $descriptor->kind);
        self::assertSame('int', $descriptor->builtin);
        self::assertFalse($descriptor->allowsNull());
    }

    public function testClassTypeFactoryProducesRequiredClassDescriptor(): void
    {
        $descriptor = TypeDescriptor::classType(UserDto::class);

        self::assertSame(TypeDescriptor::KIND_CLASS, $descriptor->kind);
        self::assertSame(UserDto::class, $descriptor->className);
        self::assertFalse($descriptor->allowsNull());
    }

    public function testMembersDefaultToAnEmptyList(): void
    {
        $descriptor = new TypeDescriptor(kind: TypeDescriptor::KIND_MIXED);

        self::assertSame([], $descriptor->members);
    }
}
