<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

final class ReflectionTypeDescriptorReader
{
    public function describe(?ReflectionType $type): TypeDescriptor
    {
        if ($type === null) {
            return $this->mixedType();
        }

        if ($type instanceof ReflectionNamedType) {
            return $this->describeNamedType($type);
        }

        if ($type instanceof ReflectionUnionType) {
            return $this->describeCompositeType($type, TypeDescriptor::KIND_UNION);
        }

        if ($type instanceof ReflectionIntersectionType) {
            return $this->describeCompositeType($type, TypeDescriptor::KIND_INTERSECTION);
        }

        return $this->mixedType();
    }

    private function describeNamedType(ReflectionNamedType $type): TypeDescriptor
    {
        $name = $type->getName();

        if (strtolower($name) === 'mixed') {
            return $this->mixedType();
        }

        $nullability = $type->allowsNull() ? TypeDescriptor::NULLABLE : TypeDescriptor::REQUIRED;

        if ($type->isBuiltin()) {
            return new TypeDescriptor(
                kind: TypeDescriptor::KIND_BUILTIN,
                builtin: $name,
                nullability: $nullability,
            );
        }

        if (enum_exists($name)) {
            return new TypeDescriptor(
                kind: TypeDescriptor::KIND_ENUM,
                className: $name,
                nullability: $nullability,
            );
        }

        if (! class_exists($name) && ! interface_exists($name)) {
            return $this->mixedType();
        }

        /** @var class-string $name */
        return new TypeDescriptor(
            kind: TypeDescriptor::KIND_CLASS,
            className: $name,
            nullability: $nullability,
        );
    }

    private function describeCompositeType(
        ReflectionUnionType | ReflectionIntersectionType $type,
        string $kind,
    ): TypeDescriptor {
        $members = [];
        foreach ($type->getTypes() as $inner) {
            $members[] = $this->describe($inner);
        }

        $nullability = TypeDescriptor::REQUIRED;
        if ($type instanceof ReflectionUnionType && $type->allowsNull()) {
            $nullability = TypeDescriptor::NULLABLE;
        }

        return new TypeDescriptor(
            kind: $kind,
            members: $members,
            nullability: $nullability,
        );
    }

    private function mixedType(): TypeDescriptor
    {
        return new TypeDescriptor(
            kind: TypeDescriptor::KIND_MIXED,
            nullability: TypeDescriptor::NULLABLE,
        );
    }
}
