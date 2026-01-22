<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Hydration\Naming\NamingStrategyInterface;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;

final class Mapper implements MapperInterface
{
    public function map(array $source, ClassMeta $meta, ?Context $ctx): array
    {
        $result = [];
        $namingStrategy = $ctx?->namingStrategy;

        foreach ($meta->properties as $property) {
            $sourceKey = $this->resolveSourceKey($property, $namingStrategy);
            $value = $this->extractValue($source, $sourceKey, $property, $namingStrategy);

            if ($value !== null || array_key_exists($sourceKey, $source) || $this->keyExistsWithStrategy($source, $property, $namingStrategy)) {
                $result[$property->name] = $value;
            }
        }

        return $result;
    }

    private function resolveSourceKey(PropertyMeta $property, ?NamingStrategyInterface $strategy): string
    {
        if ($property->mapFrom !== null) {
            return $property->mapFrom;
        }

        if ($strategy !== null) {
            return $strategy->convert($property->name, NamingStrategyInterface::DIRECTION_TO_SOURCE);
        }

        return $property->name;
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function extractValue(
        array $source,
        string $primaryKey,
        PropertyMeta $property,
        ?NamingStrategyInterface $strategy,
    ): mixed {
        if (array_key_exists($primaryKey, $source)) {
            return $source[$primaryKey];
        }

        if (array_key_exists($property->name, $source)) {
            return $source[$property->name];
        }

        if ($strategy !== null) {
            $alternateKey = $strategy->convert($property->name, NamingStrategyInterface::DIRECTION_TO_SOURCE);

            if (array_key_exists($alternateKey, $source)) {
                return $source[$alternateKey];
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private function keyExistsWithStrategy(
        array $source,
        PropertyMeta $property,
        ?NamingStrategyInterface $strategy,
    ): bool {
        if (array_key_exists($property->name, $source)) {
            return true;
        }

        if ($property->mapFrom !== null && array_key_exists($property->mapFrom, $source)) {
            return true;
        }

        if ($strategy !== null) {
            $alternateKey = $strategy->convert($property->name, NamingStrategyInterface::DIRECTION_TO_SOURCE);

            return array_key_exists($alternateKey, $source);
        }

        return false;
    }
}
