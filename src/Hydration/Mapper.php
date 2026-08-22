<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;

final class Mapper implements MapperInterface
{
    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     *
     * @throws MappingException
     */
    public function map(array $source, ClassMeta $meta, ?Context $ctx): array
    {
        $working = $this->prepareWorkingSource($source, $ctx);
        $mapped = [];

        foreach ($meta->properties as $property) {
            if (! $this->isWritableFromSource($property, $meta)) {
                continue;
            }

            $value = $this->resolvePropertyValue($property, $working, $meta, $ctx);
            if (! $value instanceof UnmappedValue) {
                $mapped[$property->name] = $value;
            }
        }

        return $mapped;
    }

    /**
     * @param  array<string, mixed>  $working
     *
     * @throws MappingException
     */
    private function resolvePropertyValue(
        PropertyMeta $property,
        array $working,
        ClassMeta $meta,
        ?Context $ctx,
    ): mixed {
        $matches = [];
        foreach ($property->sourceKeysFor($ctx?->namingStrategy) as $key) {
            if (array_key_exists($key, $working)) {
                $matches[$key] = $working[$key];
            }
        }

        if ($matches === []) {
            return new UnmappedValue();
        }

        $unique = [];
        foreach ($matches as $value) {
            $unique[serialize($value)] = $value;
        }

        if (count($unique) > 1) {
            throw new MappingException(
                message: 'Ambiguous source keys for property.',
                path: $property->name,
                expectedType: $meta->className,
                givenValue: array_keys($matches),
            );
        }

        return reset($unique);
    }

    /**
     * @param  array<string, mixed>  $source
     * @return list<string>
     *
     * @throws MappingException
     */
    public function unknownKeys(array $source, ClassMeta $meta, ?Context $ctx, ?string $discriminatorField): array
    {
        $allowed = [];
        foreach ($meta->properties as $property) {
            if (! $this->isWritableFromSource($property, $meta)) {
                continue;
            }

            foreach ($property->sourceKeysFor($ctx?->namingStrategy) as $key) {
                $allowed[$key] = true;
            }
        }

        if ($discriminatorField !== null) {
            $allowed[$discriminatorField] = true;
            $strategy = $ctx?->namingStrategy;
            if ($strategy !== null) {
                $allowed[$strategy->toProperty($discriminatorField)] = true;
                $allowed[$strategy->toSource($discriminatorField)] = true;
            }
        }

        $unknown = [];
        $working = $this->prepareWorkingSource($source, $ctx);
        foreach (array_keys($working) as $key) {
            if (! isset($allowed[$key])) {
                $unknown[] = (string) $key;
            }
        }

        return array_values(array_unique($unknown));
    }

    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     *
     * @throws MappingException
     */
    private function prepareWorkingSource(array $source, ?Context $ctx): array
    {
        $strategy = $ctx?->namingStrategy;
        if ($strategy === null) {
            return $source;
        }

        /** @var array<string, list<array{key: string, value: mixed}>> $entriesByPropertyKey */
        $entriesByPropertyKey = [];
        foreach ($source as $key => $value) {
            $propertyKey = $strategy->toProperty((string) $key);
            $entriesByPropertyKey[$propertyKey][] = ['key' => (string) $key, 'value' => $value];
        }

        $working = $source;
        foreach ($entriesByPropertyKey as $propertyKey => $entries) {
            $unique = [];
            foreach ($entries as $entry) {
                $unique[serialize($entry['value'])] = $entry['value'];
            }

            if (count($unique) > 1) {
                throw new MappingException(
                    message: 'Conflicting source keys normalize to the same property key.',
                    path: $propertyKey,
                    givenValue: array_map(static fn(array $entry): string => $entry['key'], $entries),
                );
            }

            $working[$propertyKey] = reset($unique);
        }

        return $working;
    }

    /**
     * @param  array<string, mixed>  $mapped
     */
    public function missingUsesDefaultFrom(PropertyMeta $property, array $mapped): bool
    {
        if (array_key_exists($property->name, $mapped)) {
            return false;
        }

        return $property->resolved->defaultFrom !== null;
    }

    private function isWritableFromSource(PropertyMeta $property, ClassMeta $meta): bool
    {
        if ($property->hidden !== PropertyMeta::HIDDEN) {
            return true;
        }

        $discriminator = $meta->discriminator;
        if ($discriminator === null) {
            return false;
        }

        return $property->name === $discriminator->field;
    }
}
