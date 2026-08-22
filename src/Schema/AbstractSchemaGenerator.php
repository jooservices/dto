<?php

declare(strict_types=1);

namespace JOOservices\Dto\Schema;

use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use ReflectionClass;
use ReflectionException;

abstract class AbstractSchemaGenerator implements SchemaGeneratorInterface
{
    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
        private readonly TypeSchemaBuilder $typeSchemaBuilder = new TypeSchemaBuilder(),
    ) {
    }

    /**
     * @param  class-string  $className
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function generate(string $className): array
    {
        $defs = [];
        $schema = $this->objectSchema($className, $defs);

        return $this->withDefinitions($schema, $defs);
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<string, array<string, mixed>>  $defs
     * @return array<string, mixed>
     */
    abstract protected function withDefinitions(array $schema, array $defs): array;

    abstract protected function refPrefix(): string;

    /**
     * @param  class-string  $className
     * @param  array<string, array<string, mixed>>  $defs
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    protected function objectSchema(string $className, array &$defs): array
    {
        if (interface_exists($className)) {
            return ['type' => 'object'];
        }

        $key = str_replace('\\', '.', $className);
        if (isset($defs[$key])) {
            return ['$ref' => $this->refPrefix() . $key];
        }

        $defs[$key] = ['type' => 'object'];
        $meta = $this->metaFactory->create($className);
        $properties = [];
        $required = [];
        foreach ($meta->properties as $property) {
            if ($property->hidden === PropertyMeta::HIDDEN) {
                continue;
            }

            $schemaKey = $property->resolved->mapToKey ?? $property->name;
            $properties[$schemaKey] = $this->typeSchema($property->type, $defs);
            if (! $property->allowsNull && ! $property->hasDefault) {
                $required[] = $schemaKey;
            }
        }

        $def = [
            'type' => 'object',
            'properties' => $properties,
            'required' => $required,
        ];

        $discriminator = $this->discriminatorDeclaredOnClass($className);
        if ($discriminator !== null) {
            $def = $this->applyDiscriminatorSchema($def, $discriminator, $meta, $defs);
        }

        $defs[$key] = $def;

        return ['$ref' => $this->refPrefix() . $key];
    }

    /**
     * @param  array<string, mixed>  $def
     * @param  array<string, array<string, mixed>>  $defs
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    private function applyDiscriminatorSchema(
        array $def,
        DiscriminatorMap $discriminator,
        ClassMeta $meta,
        array &$defs,
    ): array {
        /** @var array<string, array<string, mixed>> $oneOfRefs */
        $oneOfRefs = [];
        /** @var array<string, string> $mapping */
        $mapping = [];
        $baseRef = $this->refPrefix() . str_replace('\\', '.', $meta->className);

        foreach ($discriminator->map as $discriminatorValue => $targetClass) {
            if (! $this->isValidDiscriminatorTarget($meta->className, $targetClass)) {
                continue;
            }

            $refSchema = $this->objectSchema($targetClass, $defs);
            $ref = $refSchema['$ref'] ?? null;
            if (! is_string($ref)) {
                continue;
            }

            $mapping[$discriminatorValue] = $ref;
            if ($ref !== $baseRef) {
                $oneOfRefs[$ref] = ['$ref' => $ref];
            }
        }

        $propertyName = $discriminator->field;
        $fieldProperty = $meta->property($discriminator->field);
        if ($fieldProperty !== null && $fieldProperty->resolved->mapToKey !== null) {
            $propertyName = $fieldProperty->resolved->mapToKey;
        }

        $def['oneOf'] = array_values($oneOfRefs);
        $def['discriminator'] = [
            'propertyName' => $propertyName,
            'mapping' => $mapping,
        ];

        return $def;
    }

    /**
     * @param  class-string  $baseClassName
     * @param  class-string  $targetClass
     */
    private function isValidDiscriminatorTarget(string $baseClassName, string $targetClass): bool
    {
        if ($targetClass === $baseClassName) {
            return true;
        }

        return is_subclass_of($targetClass, $baseClassName)
            && is_subclass_of($targetClass, AbstractDto::class);
    }

    /**
     * @param  class-string  $className
     */
    private function discriminatorDeclaredOnClass(string $className): ?DiscriminatorMap
    {
        $reflection = new ReflectionClass($className);
        foreach ($reflection->getAttributes(DiscriminatorMap::class) as $attribute) {
            return $attribute->newInstance();
        }

        return null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $defs
     * @return array<string, mixed>|object
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    protected function typeSchema(TypeDescriptor $type, array &$defs): array | object
    {
        $objectSchema = function (string $className, array &$definitions): array {
            /** @var array<string, array<string, mixed>> $definitions */
            /** @var class-string $className */
            return $this->objectSchema($className, $definitions);
        };

        $typeSchema = function (TypeDescriptor $member, array &$definitions): array | object {
            /** @var array<string, array<string, mixed>> $definitions */
            return $this->typeSchema($member, $definitions);
        };

        return $this->typeSchemaBuilder->build($type, $defs, $objectSchema, $typeSchema);
    }
}
