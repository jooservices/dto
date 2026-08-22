<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Schema;

use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Schema\TypeSchemaBuilder;
use JOOservices\Dto\Tests\Fixtures\Priority;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class TypeSchemaBuilderTest extends TestCase
{
    public function testBuiltinStringSchema(): void
    {
        $defs = [];
        $schema = (new TypeSchemaBuilder())->build(
            TypeDescriptor::builtin('string'),
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'string'], $schema);
    }

    public function testBuiltinBoolMapsToBooleanType(): void
    {
        $defs = [];
        $schema = (new TypeSchemaBuilder())->build(
            TypeDescriptor::builtin('bool'),
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'boolean'], $schema);
    }

    public function testBuiltinIntMapsToJsonSchemaIntegerType(): void
    {
        $defs = [];
        $schema = (new TypeSchemaBuilder())->build(
            TypeDescriptor::builtin('int'),
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'integer'], $schema);
    }

    public function testBuiltinFloatMapsToJsonSchemaNumberType(): void
    {
        $defs = [];
        $schema = (new TypeSchemaBuilder())->build(
            TypeDescriptor::builtin('float'),
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'number'], $schema);
    }

    public function testNullableBuiltinProducesAnyOfWithNullType(): void
    {
        $defs = [];
        $type = new TypeDescriptor(
            kind: TypeDescriptor::KIND_BUILTIN,
            builtin: 'string',
            nullability: TypeDescriptor::NULLABLE,
        );
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['anyOf' => [['type' => 'string'], ['type' => 'null']]], $schema);
    }

    public function testStringBackedEnumUsesStringTypeWithEnumValues(): void
    {
        $defs = [];
        $type = new TypeDescriptor(
            kind: TypeDescriptor::KIND_ENUM,
            className: Status::class,
        );
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'string', 'enum' => ['active', 'inactive']], $schema);
    }

    public function testIntBackedEnumUsesIntegerTypeWithEnumValues(): void
    {
        $defs = [];
        $type = new TypeDescriptor(
            kind: TypeDescriptor::KIND_ENUM,
            className: Priority::class,
        );
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'integer', 'enum' => [1, 10]], $schema);
    }

    public function testEnumWithoutClassNameFallsBackToStringType(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_ENUM);
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'string'], $schema);
    }

    public function testClassKindWithoutClassNameFallsBackToBuiltinSchema(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_CLASS, className: null);
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => ['$ref' => 'unused'],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(['type' => 'string'], $schema);
    }

    public function testClassKindWithClassNameDelegatesToObjectSchemaCallback(): void
    {
        $defs = [];
        $seenClassName = null;
        $objectSchema = static function (string $className, array &$defs) use (&$seenClassName): array {
            $seenClassName = $className;

            return ['$ref' => '#/$defs/' . $className];
        };

        $schema = (new TypeSchemaBuilder())->build(
            TypeDescriptor::classType(TypeSchemaBuilder::class),
            $defs,
            $objectSchema,
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(TypeSchemaBuilder::class, $seenClassName);
        self::assertSame(['$ref' => '#/$defs/' . TypeSchemaBuilder::class], $schema);
    }

    public function testNullableClassKindWrapsObjectSchemaInAnyOfWithNull(): void
    {
        $defs = [];
        $type = new TypeDescriptor(
            kind: TypeDescriptor::KIND_CLASS,
            className: TypeSchemaBuilder::class,
            nullability: TypeDescriptor::NULLABLE,
        );
        $objectSchema = static fn(string $className, array &$defs): array => ['$ref' => '#/$defs/' . $className];

        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            $objectSchema,
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertSame(
            [
                'anyOf' => [
                    ['$ref' => '#/$defs/' . TypeSchemaBuilder::class],
                    ['type' => 'null'],
                ],
            ],
            $schema,
        );
    }

    public function testMixedKindAllowsAnyValueAndNullWhenNullable(): void
    {
        $defs = [];
        $schema = (new TypeSchemaBuilder())->build(
            TypeDescriptor::mixed(),
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertIsArray($schema);
        self::assertSame('{"anyOf":[{},{"type":"null"}]}', json_encode($schema));
    }

    public function testNonNullableMixedKindAllowsAnyValue(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_MIXED, nullability: TypeDescriptor::REQUIRED);
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertInstanceOf(stdClass::class, $schema);
        self::assertSame('{}', json_encode($schema));
    }

    public function testIntersectionKindUsesAllOf(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_INTERSECTION, members: [
            TypeDescriptor::classType(TypeSchemaBuilder::class),
            TypeDescriptor::builtin('string'),
        ]);
        $typeSchema = static function (TypeDescriptor $member, array &$defs): array {
            if ($member->kind === TypeDescriptor::KIND_CLASS && $member->className !== null) {
                return ['$ref' => '#/$defs/' . $member->className];
            }

            return ['type' => 'string'];
        };

        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => ['$ref' => '#/$defs/' . $c],
            $typeSchema,
        );

        self::assertSame(
            [
                'allOf' => [
                    ['$ref' => '#/$defs/' . TypeSchemaBuilder::class],
                    ['type' => 'string'],
                ],
            ],
            $schema,
        );
    }

    public function testUnionSchemaDelegatesEachMemberToTypeSchemaCallback(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_UNION, members: [
            TypeDescriptor::builtin('string'),
            TypeDescriptor::builtin('int'),
        ]);
        $typeSchema = static fn(TypeDescriptor $member, array &$defs): array => match ($member->builtin) {
            'string' => ['type' => 'string'],
            default => ['type' => 'integer'],
        };

        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            $typeSchema,
        );

        self::assertSame(['anyOf' => [['type' => 'string'], ['type' => 'integer']]], $schema);
    }

    public function testArraySchemaWithItemTypeDelegatesToTypeSchemaCallback(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_ARRAY, members: [TypeDescriptor::builtin('string')]);
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => ['type' => 'string'],
        );

        self::assertSame(['type' => 'array', 'items' => ['type' => 'string']], $schema);
    }

    public function testArraySchemaWithoutItemTypeEmitsEmptyObjectItemsSchema(): void
    {
        $defs = [];
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_ARRAY, members: []);
        $schema = (new TypeSchemaBuilder())->build(
            $type,
            $defs,
            static fn(string $c, array &$d): array => [],
            static fn(TypeDescriptor $t, array &$d): array => [],
        );

        self::assertIsArray($schema);
        self::assertSame('array', $schema['type']);
        self::assertInstanceOf(stdClass::class, $schema['items']);
        self::assertSame('{"type":"array","items":{}}', json_encode($schema));
    }
}
