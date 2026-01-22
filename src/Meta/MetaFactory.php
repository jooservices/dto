<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\TransformWith;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;
use ReflectionProperty;

class MetaFactory implements MetaFactoryInterface
{
    public function __construct(
        private readonly MetaCacheInterface $cache,
    ) {}

    public function create(string $className): ClassMeta
    {
        if ($this->cache->has($className)) {
            $cached = $this->cache->get($className);

            if ($cached !== null) {
                return $cached;
            }
        }

        $meta = $this->buildClassMeta($className);
        $this->cache->set($className, $meta);

        return $meta;
    }

    /**
     * @param  class-string  $className
     */
    private function buildClassMeta(string $className): ClassMeta
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        $constructorParams = [];

        /** @var array<string, ReflectionParameter> $constructorParamMap */
        $constructorParamMap = [];

        if ($constructor !== null) {
            foreach ($constructor->getParameters() as $param) {
                $constructorParams[] = $param->getName();
                $constructorParamMap[$param->getName()] = $param;
            }
        }

        $properties = [];

        foreach ($reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic()) {
                continue;
            }

            $constructorParam = $constructorParamMap[$property->getName()] ?? null;
            $propertyMeta = $this->buildPropertyMeta($property, $constructorParam);
            $properties[$property->getName()] = $propertyMeta;
        }

        $classAttributes = $this->extractAttributes($reflection->getAttributes());

        return new ClassMeta(
            className: $className,
            isReadonly: $reflection->isReadOnly(),
            properties: $properties,
            constructorParams: $constructorParams,
            attributes: $classAttributes,
        );
    }

    private function buildPropertyMeta(ReflectionProperty $property, ?ReflectionParameter $constructorParam = null): PropertyMeta
    {
        $attributes = $property->getAttributes();
        $extractedAttributes = $this->extractAttributes($attributes);

        $mapFrom = $this->extractMapFrom($attributes);
        $casterClass = $this->extractCasterClass($attributes);
        $transformerClass = $this->extractTransformerClass($attributes);
        $isHidden = $this->extractIsHidden($attributes);
        $validationRules = $this->extractValidationRules($attributes);

        // Check both property default and constructor parameter default (for promoted properties)
        $hasDefault = $property->hasDefaultValue();
        $defaultValue = $hasDefault ? $property->getDefaultValue() : null;

        // For promoted properties, also check constructor parameter default
        if (! $hasDefault && $constructorParam !== null && $constructorParam->isDefaultValueAvailable()) {
            $hasDefault = true;
            $defaultValue = $constructorParam->getDefaultValue();
        }

        return new PropertyMeta(
            name: $property->getName(),
            type: TypeDescriptor::fromReflection($property->getType()),
            isReadonly: $property->isReadOnly(),
            hasDefault: $hasDefault,
            defaultValue: $defaultValue,
            mapFrom: $mapFrom,
            casterClass: $casterClass,
            transformerClass: $transformerClass,
            isHidden: $isHidden,
            validationRules: $validationRules,
            attributes: $extractedAttributes,
        );
    }

    /**
     * @param  array<ReflectionAttribute<object>>  $attributes
     * @return array<object>
     */
    private function extractAttributes(array $attributes): array
    {
        $result = [];

        foreach ($attributes as $attribute) {
            $result[] = $attribute->newInstance();
        }

        return $result;
    }

    /**
     * @param  array<ReflectionAttribute<object>>  $attributes
     */
    private function extractMapFrom(array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === MapFrom::class) {
                /** @var MapFrom $instance */
                $instance = $attribute->newInstance();

                return $instance->key;
            }
        }

        return null;
    }

    /**
     * @param  array<ReflectionAttribute<object>>  $attributes
     * @return class-string|null
     */
    private function extractCasterClass(array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === CastWith::class) {
                /** @var CastWith $instance */
                $instance = $attribute->newInstance();

                return $instance->casterClass;
            }
        }

        return null;
    }

    /**
     * @param  array<ReflectionAttribute<object>>  $attributes
     * @return class-string|null
     */
    private function extractTransformerClass(array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === TransformWith::class) {
                /** @var TransformWith $instance */
                $instance = $attribute->newInstance();

                return $instance->transformerClass;
            }
        }

        return null;
    }

    /**
     * @param  array<ReflectionAttribute<object>>  $attributes
     */
    private function extractIsHidden(array $attributes): bool
    {
        foreach ($attributes as $attribute) {
            if ($attribute->getName() === Hidden::class) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<ReflectionAttribute<object>>  $attributes
     * @return array<object>
     */
    private function extractValidationRules(array $attributes): array
    {
        $rules = [];

        foreach ($attributes as $attribute) {
            $name = $attribute->getName();

            if (str_starts_with($name, 'JOOservices\\Dto\\Attributes\\Validation\\')) {
                $rules[] = $attribute->newInstance();
            }
        }

        return $rules;
    }
}
