<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

final readonly class PropertyMeta
{
    /**
     * @param  array<object>  $validationRules
     * @param  array<object>  $attributes
     */
    public function __construct(
        public string $name,
        public TypeDescriptor $type,
        public bool $isReadonly,
        public bool $hasDefault,
        public mixed $defaultValue,
        public ?string $mapFrom,
        public ?string $casterClass,
        public ?string $transformerClass,
        public bool $isHidden,
        public array $validationRules,
        public array $attributes,
    ) {}

    public function getSourceKey(): string
    {
        return $this->mapFrom ?? $this->name;
    }

    public function requiresCasting(): bool
    {
        return $this->casterClass !== null;
    }

    public function requiresTransformation(): bool
    {
        return $this->transformerClass !== null;
    }

    public function hasValidationRules(): bool
    {
        return $this->validationRules !== [];
    }

    public function isRequired(): bool
    {
        return ! $this->hasDefault && ! $this->type->isNullable;
    }

    public function canBeNull(): bool
    {
        return $this->type->isNullable;
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
}
