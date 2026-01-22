<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Casting\CasterRegistryInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\JdtoException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Validation\ValidationContext;
use JOOservices\Dto\Validation\ValidatorRegistryInterface;
use ReflectionClass;

class Hydrator implements HydratorInterface
{
    public function __construct(
        private readonly MapperInterface $mapper,
        private readonly CasterRegistryInterface $casterRegistry,
        private readonly MetaFactoryInterface $metaFactory,
        private readonly ?ValidatorRegistryInterface $validatorRegistry = null,
    ) {}

    public function hydrate(ClassMeta $meta, array $data, ?Context $ctx): object
    {
        $mappedData = $this->mapper->map($data, $meta, $ctx);
        $constructorArgs = $this->buildConstructorArgs($meta, $mappedData, $ctx);

        $reflection = new ReflectionClass($meta->className);

        return $reflection->newInstanceArgs($constructorArgs);
    }

    /**
     * @param  array<string, mixed>  $mappedData
     * @return array<mixed>
     */
    private function buildConstructorArgs(ClassMeta $meta, array $mappedData, ?Context $ctx): array
    {
        $args = [];

        /** @var array<JdtoException> $errors */
        $errors = [];

        foreach ($meta->constructorParams as $paramName) {
            $property = $meta->getProperty($paramName);

            if ($property === null) {
                continue;
            }

            try {
                $args[] = $this->resolvePropertyValue($property, $mappedData, $ctx);
            } catch (JdtoException $e) {
                $errors[] = $e->prependPath($paramName);
            }
        }

        if ($errors !== []) {
            throw HydrationException::fromErrors(
                "Failed to hydrate {$meta->className}",
                $errors,
            );
        }

        return $args;
    }

    /**
     * @param  array<string, mixed>  $mappedData
     *
     * @throws JdtoException
     */
    private function resolvePropertyValue(PropertyMeta $property, array $mappedData, ?Context $ctx): mixed
    {
        $hasValue = array_key_exists($property->name, $mappedData);
        $value = $hasValue ? $mappedData[$property->name] : null;

        if (! $hasValue && $property->hasDefault) {
            return $property->defaultValue;
        }

        if (! $hasValue && $property->isRequired()) {
            throw MappingException::missingRequiredKey($property->name, $property->name);
        }

        // Validate BEFORE casting (as per implementation plan)
        // Must validate before early return for nullable, to support RequiredIf
        $this->validateValue($property, $value, $mappedData, $ctx);

        if ($value === null && $property->canBeNull()) {
            return null;
        }

        return $this->castValue($property, $value, $ctx);
    }

    /**
     * Validate a property value if validation is enabled.
     *
     * @param  array<string, mixed>  $allData  All input data for conditional validators
     */
    private function validateValue(PropertyMeta $property, mixed $value, array $allData, ?Context $ctx): void
    {
        if ($ctx === null || ! $ctx->validationEnabled) {
            return;
        }

        if ($this->validatorRegistry === null) {
            return;
        }

        if (! $property->hasValidationRules()) {
            return;
        }

        $validationContext = new ValidationContext(
            property: $property,
            allData: $allData,
            context: $ctx,
        );

        $this->validatorRegistry->validate($property, $value, $validationContext);
    }

    private function castValue(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($property->type->isDto && is_array($value)) {
            /** @var class-string $className */
            $className = $property->type->name;

            /** @var array<string, mixed> $value */
            return $this->hydrateNested($className, $value, $ctx);
        }

        if ($property->type->isTypedArray() && is_array($value)) {
            return $this->hydrateTypedArray($property, $value, $ctx);
        }

        // Priority 1: Use attribute-specified caster (#[CastWith])
        if ($property->casterClass !== null) {
            return $this->castWithAttributeCaster($property, $value, $ctx);
        }

        // Priority 2: Use registry-based caster
        if ($this->casterRegistry->canCast($property, $value)) {
            return $this->casterRegistry->cast($property, $value, $ctx);
        }

        // Check if value type matches target type - if so, return as is
        if ($this->isValueTypeCompatible($property, $value)) {
            return $value;
        }

        // In permissive mode, return null for nullable properties when no caster can handle the value
        if (($ctx?->isPermissiveMode() ?? false) && $property->canBeNull()) {
            return null;
        }

        // Throw exception for incompatible types in non-permissive mode
        throw CastException::cannotCast($value, $property->type->name, $property->name);
    }

    /**
     * Cast using the caster class specified via #[CastWith] attribute.
     */
    private function castWithAttributeCaster(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        /** @var class-string<CasterInterface> $casterClass */
        $casterClass = $property->casterClass;

        /** @var CasterInterface $caster */
        $caster = new $casterClass;

        return $caster->cast($property, $value, $ctx);
    }

    /**
     * @param  class-string  $className
     * @param  array<string, mixed>  $data
     */
    private function hydrateNested(string $className, array $data, ?Context $ctx): object
    {
        $nestedMeta = $this->metaFactory->create($className);

        return $this->hydrate($nestedMeta, $data, $ctx);
    }

    /**
     * @param  array<mixed>  $values
     * @return array<mixed>
     */
    private function hydrateTypedArray(PropertyMeta $property, array $values, ?Context $ctx): array
    {
        $itemType = $property->type->arrayItemType;

        if ($itemType === null) {
            return $values;
        }

        $result = [];

        foreach ($values as $key => $value) {
            if ($itemType->isDto && is_array($value)) {
                /** @var class-string $className */
                $className = $itemType->name;

                /** @var array<string, mixed> $value */
                $result[$key] = $this->hydrateNested($className, $value, $ctx);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Check if the value type is compatible with the property type without casting.
     */
    private function isValueTypeCompatible(PropertyMeta $property, mixed $value): bool
    {
        $targetType = $property->type->name;

        return match ($targetType) {
            'int' => is_int($value),
            'float' => is_float($value) || is_int($value),
            'string' => is_string($value),
            'bool' => is_bool($value),
            'array' => is_array($value),
            'object' => is_object($value),
            'mixed' => true,
            default => $value instanceof $targetType,
        };
    }
}
