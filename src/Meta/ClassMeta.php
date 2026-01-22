<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

class ClassMeta
{
    /**
     * @param  class-string  $className
     * @param  array<string, PropertyMeta>  $properties
     * @param  array<string>  $constructorParams
     * @param  array<object>  $attributes
     */
    public function __construct(
        public readonly string $className,
        public readonly bool $isReadonly,
        public readonly array $properties,
        public readonly array $constructorParams,
        public readonly array $attributes,
    ) {}

    public function hasProperty(string $name): bool
    {
        return isset($this->properties[$name]);
    }

    public function getProperty(string $name): ?PropertyMeta
    {
        return $this->properties[$name] ?? null;
    }

    /**
     * @return array<PropertyMeta>
     */
    public function getRequiredProperties(): array
    {
        return array_filter(
            $this->properties,
            static fn (PropertyMeta $prop): bool => $prop->isRequired(),
        );
    }

    /**
     * @return array<PropertyMeta>
     */
    public function getOptionalProperties(): array
    {
        return array_filter(
            $this->properties,
            static fn (PropertyMeta $prop): bool => ! $prop->isRequired(),
        );
    }

    /**
     * @return array<PropertyMeta>
     */
    public function getHiddenProperties(): array
    {
        return array_filter(
            $this->properties,
            static fn (PropertyMeta $prop): bool => $prop->isHidden,
        );
    }

    /**
     * @return array<PropertyMeta>
     */
    public function getVisibleProperties(): array
    {
        return array_filter(
            $this->properties,
            static fn (PropertyMeta $prop): bool => ! $prop->isHidden,
        );
    }

    public function getPropertyCount(): int
    {
        return count($this->properties);
    }

    /**
     * @return array<string>
     */
    public function getPropertyNames(): array
    {
        return array_keys($this->properties);
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $attributeClass
     * @return T|null
     */
    public function getAttribute(string $attributeClass): ?object
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute instanceof $attributeClass) {
                return $attribute;
            }
        }

        return null;
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $attributeClass
     */
    public function hasAttribute(string $attributeClass): bool
    {
        return $this->getAttribute($attributeClass) !== null;
    }

    public function isConstructorBased(): bool
    {
        return $this->constructorParams !== [];
    }
}
