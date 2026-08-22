<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

use JOOservices\Dto\Meta\TypeDescriptor;
use ReflectionEnum;
use ReflectionEnumBackedCase;
use ReflectionEnumUnitCase;
use ReflectionException;
use ReflectionNamedType;
use stdClass;
use UnitEnum;

final class TypeSchemaBuilder
{
    /**
     * @param  array<string, array<string, mixed>>  $defs
     * @param  callable(class-string, array<string, array<string, mixed>>): array<string, mixed>  $objectSchema
     * @param  callable(TypeDescriptor, array<string, array<string, mixed>>): (array<string, mixed>|object)  $typeSchema
     * @return array<string, mixed>|object
     */
    public function build(
        TypeDescriptor $type,
        array &$defs,
        callable $objectSchema,
        callable $typeSchema,
    ): array | object {
        return match ($type->kind) {
            TypeDescriptor::KIND_UNION => $this->unionSchema($type, $defs, $typeSchema),
            TypeDescriptor::KIND_ARRAY => $this->arraySchema($type, $defs, $typeSchema),
            TypeDescriptor::KIND_INTERSECTION => $this->intersectionSchema($type, $defs, $typeSchema),
            TypeDescriptor::KIND_MIXED => $this->mixedSchema($type),
            TypeDescriptor::KIND_CLASS => $type->className === null
                ? $this->builtinSchema($type)
                : $this->classSchema($type, $defs, $objectSchema),
            TypeDescriptor::KIND_ENUM => $this->enumSchema($type),
            default => $this->builtinSchema($type),
        };
    }

    /**
     * @param  array<string, array<string, mixed>>  $defs
     * @param  callable(class-string, array<string, array<string, mixed>>): array<string, mixed>  $objectSchema
     * @return array<string, mixed>
     */
    private function classSchema(TypeDescriptor $type, array &$defs, callable $objectSchema): array
    {
        /** @var class-string $className */
        $className = $type->className;
        $schema = $objectSchema($className, $defs);

        if ($type->allowsNull()) {
            return ['anyOf' => [$schema, ['type' => 'null']]];
        }

        return $schema;
    }

    /**
     * @param  array<string, array<string, mixed>>  $defs
     * @param  callable(TypeDescriptor, array<string, array<string, mixed>>): (array<string, mixed>|object)  $typeSchema
     * @return array<string, mixed>
     */
    private function unionSchema(TypeDescriptor $type, array &$defs, callable $typeSchema): array
    {
        $anyOf = [];
        foreach ($type->members as $member) {
            $anyOf[] = $typeSchema($member, $defs);
        }

        return ['anyOf' => $anyOf];
    }

    /**
     * @param  array<string, array<string, mixed>>  $defs
     * @param  callable(TypeDescriptor, array<string, array<string, mixed>>): (array<string, mixed>|object)  $typeSchema
     * @return array<string, mixed>
     */
    private function arraySchema(TypeDescriptor $type, array &$defs, callable $typeSchema): array
    {
        $items = $type->members[0] ?? null;

        return [
            'type' => 'array',
            'items' => $items === null ? new stdClass() : $typeSchema($items, $defs),
        ];
    }

    /**
     * @param  array<string, array<string, mixed>>  $defs
     * @param  callable(TypeDescriptor, array<string, array<string, mixed>>): (array<string, mixed>|object)  $typeSchema
     * @return array<string, mixed>
     */
    private function intersectionSchema(TypeDescriptor $type, array &$defs, callable $typeSchema): array
    {
        $allOf = [];
        foreach ($type->members as $member) {
            $allOf[] = $typeSchema($member, $defs);
        }

        return ['allOf' => $allOf];
    }

    /**
     * @return array<string, mixed>|object
     */
    private function mixedSchema(TypeDescriptor $type): array | object
    {
        if ($type->allowsNull()) {
            return ['anyOf' => [new stdClass(), ['type' => 'null']]];
        }

        return new stdClass();
    }

    /**
     * @return array<string, mixed>
     */
    private function enumSchema(TypeDescriptor $type): array
    {
        if ($type->className === null || ! enum_exists($type->className)) {
            $schema = ['type' => 'string'];

            return $type->allowsNull()
                ? ['anyOf' => [$schema, ['type' => 'null']]]
                : $schema;
        }

        try {
            $reflection = new ReflectionEnum($type->className);
        } catch (ReflectionException) {
            $schema = ['type' => 'string'];

            return $type->allowsNull()
                ? ['anyOf' => [$schema, ['type' => 'null']]]
                : $schema;
        }

        $schema = [
            'type' => $this->enumJsonSchemaType($reflection),
            'enum' => $this->enumCaseValues($reflection),
        ];

        if ($type->allowsNull()) {
            return ['anyOf' => [$schema, ['type' => 'null']]];
        }

        return $schema;
    }

    /**
     * @param  ReflectionEnum<UnitEnum>  $reflection
     * @return list<int|string>
     */
    private function enumCaseValues(ReflectionEnum $reflection): array
    {
        if ($reflection->isBacked()) {
            $cases = [];
            /** @var list<ReflectionEnumBackedCase> $backedCases */
            $backedCases = $reflection->getCases();
            foreach ($backedCases as $case) {
                $cases[] = $case->getBackingValue();
            }

            return $cases;
        }

        /** @var list<ReflectionEnumUnitCase> $unitCases */
        $unitCases = $reflection->getCases();

        return array_map(
            static fn(ReflectionEnumUnitCase $case): string => $case->name,
            $unitCases,
        );
    }

    /**
     * @param  ReflectionEnum<UnitEnum>  $reflection
     */
    private function enumJsonSchemaType(ReflectionEnum $reflection): string
    {
        if (! $reflection->isBacked()) {
            return 'string';
        }

        $backingType = $reflection->getBackingType();
        if (! $backingType instanceof ReflectionNamedType) {
            return 'string';
        }

        return $this->jsonSchemaBuiltinType($backingType->getName());
    }

    /**
     * @return array<string, mixed>
     */
    private function builtinSchema(TypeDescriptor $type): array
    {
        $builtin = $type->builtin ?? 'string';
        $schema = ['type' => $this->jsonSchemaBuiltinType($builtin)];
        if ($type->allowsNull()) {
            return ['anyOf' => [$schema, ['type' => 'null']]];
        }

        return $schema;
    }

    private function jsonSchemaBuiltinType(string $builtin): string
    {
        return match ($builtin) {
            'bool' => 'boolean',
            'int' => 'integer',
            'float' => 'number',
            default => $builtin,
        };
    }
}
