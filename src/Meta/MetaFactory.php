<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Exceptions\HydrationException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

final class MetaFactory implements MetaFactoryInterface
{
    public function __construct(
        private readonly MetaCacheInterface $cache = new MemoryMetaCache(),
        private readonly MetaFactoryPropertyReader $propertyReader = new MetaFactoryPropertyReader(),
    ) {
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function create(string $className): ClassMeta
    {
        $cached = $this->cache->get($className);
        if ($cached !== null) {
            return $cached;
        }

        if (! class_exists($className)) {
            throw new HydrationException(
                message: 'Class does not exist: ' . $className,
                expectedType: $className,
            );
        }

        $meta = $this->buildMeta($className);
        $this->cache->set($className, $meta);

        return $meta;
    }

    /**
     * @param  class-string  $className
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    private function buildMeta(string $className): ClassMeta
    {
        $reflection = new ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        $hasConstructor = $constructor !== null;
        $parameterNames = $hasConstructor
            ? array_map(
                static fn(ReflectionParameter $parameter): string => $parameter->getName(),
                $constructor->getParameters(),
            )
            : [];
        $properties = $this->propertyReader->readPromotedProperties($reflection, $className);
        $discriminator = $this->readDiscriminator($reflection);

        return new ClassMeta(
            className: $className,
            properties: $properties,
            ctorParams: $parameterNames,
            hasConstructor: $hasConstructor,
            discriminator: $discriminator,
        );
    }

    /**
     * @param  ReflectionClass<object>  $reflection
     */
    private function readDiscriminator(ReflectionClass $reflection): ?DiscriminatorMap
    {
        $current = $reflection;
        while ($current !== false) {
            foreach ($current->getAttributes(DiscriminatorMap::class) as $attribute) {
                return $attribute->newInstance();
            }

            $current = $current->getParentClass();
        }

        return null;
    }
}
