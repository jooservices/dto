<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\TypeParsing\DocBlockArrayParser;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use ReflectionProperty;

final class MetaFactoryPropertyReader
{
    public function __construct(
        private readonly DocBlockArrayParser $arrayParser = new DocBlockArrayParser(),
        private readonly ReflectionTypeDescriptorReader $typeReader = new ReflectionTypeDescriptorReader(),
        private readonly PropertyMetaAttributeResolver $attributeResolver = new PropertyMetaAttributeResolver(),
        private readonly PropertyMetaVisibilityResolver $visibilityResolver = new PropertyMetaVisibilityResolver(),
    ) {
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @param  class-string  $className
     * @return list<PropertyMeta>
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function readPromotedProperties(ReflectionClass $reflection, string $className): array
    {
        $constructor = $reflection->getConstructor();
        if ($constructor === null) {
            return [];
        }

        $properties = [];
        foreach ($constructor->getParameters() as $parameter) {
            $properties[] = $this->readPromotedProperty($reflection, $parameter, $className);
        }

        return $properties;
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @param  class-string  $className
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    private function readPromotedProperty(
        ReflectionClass $reflection,
        ReflectionParameter $parameter,
        string $className,
    ): PropertyMeta {
        $name = $parameter->getName();
        $this->assertPromotedPublic($reflection, $parameter, $className);

        $property = $reflection->getProperty($name);
        $descriptor = $this->resolveDescriptor($property, $parameter, $className);
        $attributes = array_map(
            static fn($attribute): object => $attribute->newInstance(),
            $property->getAttributes(),
        );
        $this->visibilityResolver->assertHiddenRedactCompatible($name, $attributes);

        return new PropertyMeta(
            name: $name,
            type: $descriptor,
            allowsNull: $parameter->getType() === null || $parameter->getType()->allowsNull(),
            hasDefault: $parameter->isDefaultValueAvailable(),
            defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            hidden: $this->visibilityResolver->resolveHidden($attributes),
            attributes: $attributes,
            redactWith: $this->visibilityResolver->resolveRedact($attributes),
            resolved: $this->attributeResolver->resolve($name, $attributes),
        );
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     * @param  class-string  $className
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    private function assertPromotedPublic(
        ReflectionClass $reflection,
        ReflectionParameter $parameter,
        string $className,
    ): void {
        $name = $parameter->getName();
        if (! $parameter->isPromoted()) {
            throw new HydrationException(
                message: 'DTO constructor parameters must be public promoted properties.',
                path: $name,
                expectedType: $className,
            );
        }

        if (! $reflection->getProperty($name)->isPublic()) {
            throw new HydrationException(
                message: 'DTO promoted properties must be public.',
                path: $name,
                expectedType: $className,
            );
        }
    }

    /**
     * @param  class-string  $className
     */
    private function resolveDescriptor(
        ReflectionProperty $property,
        ReflectionParameter $parameter,
        string $className,
    ): TypeDescriptor {
        $descriptor = $this->typeReader->describe($parameter->getType());
        $arrayItem = $this->arrayParser->arrayItemType(
            $property->getDocComment() === false ? null : $property->getDocComment(),
            $className,
        );
        if ($descriptor->kind === TypeDescriptor::KIND_BUILTIN && $descriptor->builtin === 'array') {
            return new TypeDescriptor(
                kind: TypeDescriptor::KIND_ARRAY,
                members: $arrayItem === null ? [] : [$arrayItem],
                nullability: $descriptor->nullability,
            );
        }

        return $descriptor;
    }
}
