<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\NestedDtoFactory;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use ReflectionException;

final class ValueTypeCaster
{
    public function __construct(
        private readonly CasterRegistryInterface $registry,
        private readonly NestedDtoFactory $nestedDtos,
    ) {
    }

    /**
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        if ($type->kind === TypeDescriptor::KIND_UNION) {
            return $this->castUnion($type, $property, $value, $ctx);
        }

        if ($this->isArrayType($type)) {
            return $this->castArray($type, $property, $value, $ctx);
        }

        if ($type->kind === TypeDescriptor::KIND_CLASS && $type->className !== null) {
            return $this->castClass($type, $property, $value, $ctx);
        }

        if ($type->kind === TypeDescriptor::KIND_INTERSECTION) {
            return $this->castIntersection($type, $property, $value);
        }

        return $this->castViaRegistry($type, $property, $value, $ctx);
    }

    public function isExactMatch(TypeDescriptor $member, mixed $value): bool
    {
        if ($this->isArrayType($member)) {
            return is_array($value);
        }

        if ($member->kind === TypeDescriptor::KIND_BUILTIN) {
            return match ($member->builtin) {
                'int' => is_int($value),
                'float' => is_float($value),
                'string' => is_string($value),
                'bool' => is_bool($value),
                default => false,
            };
        }

        if ($member->className !== null) {
            return $value instanceof $member->className;
        }

        return false;
    }

    /**
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function castUnion(TypeDescriptor $union, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        $bestIndex = null;
        $bestRank = PHP_INT_MAX;

        foreach ($union->members as $index => $member) {
            $rank = $this->unionRank($member, $value);
            if ($rank < $bestRank) {
                $bestRank = $rank;
                $bestIndex = $index;
            }
        }

        $ordered = [];
        if ($bestIndex !== null) {
            $ordered[] = $union->members[$bestIndex];
        }

        foreach ($union->members as $index => $member) {
            if ($index === $bestIndex) {
                continue;
            }

            $ordered[] = $member;
        }

        $last = null;
        foreach ($ordered as $member) {
            try {
                return $this->cast($member, $property, $value, $ctx);
            } catch (CastException $exception) {
                $last = $exception;
            }
        }

        if ($last instanceof CastException) {
            throw $last;
        }

        throw new CastException(
            message: 'Union cast failed.',
            path: $property->name,
            givenType: get_debug_type($value),
            givenValue: $property->redactWith ?? $value,
        );
    }

    /**
     * @throws CastException
     */
    private function castIntersection(TypeDescriptor $type, PropertyMeta $property, mixed $value): mixed
    {
        foreach ($type->members as $member) {
            if (! $this->isExactMatch($member, $value)) {
                throw new CastException(
                    message: 'Value does not satisfy intersection type.',
                    path: $property->name,
                    expectedType: 'intersection',
                    givenType: get_debug_type($value),
                    givenValue: $property->redactWith ?? $value,
                );
            }
        }

        return $value;
    }

    private function unionRank(TypeDescriptor $member, mixed $value): int
    {
        if ($this->isExactMatch($member, $value)) {
            return 0;
        }

        if ($member->kind === TypeDescriptor::KIND_BUILTIN && $member->builtin === 'int') {
            return 1;
        }

        if ($member->kind === TypeDescriptor::KIND_BUILTIN && $member->builtin === 'float') {
            return 2;
        }

        return 10;
    }

    /**
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function castArray(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        if (! is_array($value)) {
            throw new CastException(
                message: 'Expected array.',
                path: $property->name,
                expectedType: 'array',
                givenType: get_debug_type($value),
                givenValue: $property->redactWith ?? $value,
            );
        }

        $itemType = $type->members[0] ?? null;
        if ($itemType === null || $itemType->kind === TypeDescriptor::KIND_MIXED) {
            return $value;
        }

        $casted = [];
        foreach ($value as $offset => $item) {
            $casted[$offset] = $this->cast($itemType, $property, $item, $ctx);
        }

        return $casted;
    }

    /**
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function castClass(
        TypeDescriptor $type,
        PropertyMeta $property,
        mixed $value,
        ?Context $ctx,
    ): mixed {
        $className = $type->className;
        if ($className === null) {
            throw new CastException(
                message: 'Unable to hydrate nested class.',
                path: $property->name,
                givenType: get_debug_type($value),
                givenValue: $property->redactWith ?? $value,
            );
        }

        if (is_object($value) && is_a($value, $className)) {
            return $value;
        }

        $caster = $this->registry->firstMatching($type, $property, $value, $ctx);
        if ($caster !== null) {
            return $caster->cast($type, $property, $value, $ctx);
        }

        if (! is_array($value) && ! is_object($value) && ! is_string($value)) {
            throw new CastException(
                message: 'Unable to hydrate nested class.',
                path: $property->name,
                expectedType: $className,
                givenType: get_debug_type($value),
                givenValue: $property->redactWith ?? $value,
            );
        }

        return $this->nestedDtos->create($className, $value, $ctx);
    }

    /**
     * @throws CastException
     * @throws InvalidArgumentException
     * @throws HydrationException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    private function castViaRegistry(
        TypeDescriptor $type,
        PropertyMeta $property,
        mixed $value,
        ?Context $ctx,
    ): mixed {
        $caster = $this->registry->firstMatching($type, $property, $value, $ctx);
        if ($caster === null) {
            if ($type->kind === TypeDescriptor::KIND_MIXED) {
                return $value;
            }

            throw new CastException(
                message: 'No caster matched the value.',
                path: $property->name,
                expectedType: $type->className ?? $type->builtin,
                givenType: get_debug_type($value),
                givenValue: $property->redactWith ?? $value,
            );
        }

        return $caster->cast($type, $property, $value, $ctx);
    }

    /**
     * Reflection describes a native `array` hint as KIND_BUILTIN. `@var` item
     * types upgrade the property to KIND_ARRAY. Union members stay builtin.
     */
    private function isArrayType(TypeDescriptor $type): bool
    {
        return $type->kind === TypeDescriptor::KIND_ARRAY
            || ($type->kind === TypeDescriptor::KIND_BUILTIN && $type->builtin === 'array');
    }
}
