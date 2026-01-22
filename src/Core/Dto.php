<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;
use JOOservices\Dto\Core\Traits\CreatesCollections;
use JOOservices\Dto\Core\Traits\CreatesFromSource;
use JOOservices\Dto\Core\Traits\NormalizesToOutput;
use JsonSerializable;
use ReflectionClass;

abstract class Dto implements JsonSerializable
{
    use CreatesCollections;
    use CreatesFromSource;
    use NormalizesToOutput;

    /**
     * Transform input data before hydration (static context).
     * Override to modify/validate raw input data before DTO instantiation.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function transformInput(array $data): array
    {
        return $data;
    }

    /**
     * Create a new instance with modified property values (immutable update).
     *
     * @param  array<string, mixed>  $values
     */
    public function with(array $values): static
    {
        $reflection = new ReflectionClass($this);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            $clone = clone $this;

            foreach ($values as $name => $value) {
                /** @phpstan-ignore property.dynamicName */
                $clone->{$name} = $value;
            }

            return $clone;
        }

        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $values)) {
                $args[] = $values[$name];
            } else {
                /** @phpstan-ignore property.dynamicName */
                $args[] = $this->{$name};
            }
        }

        return $reflection->newInstanceArgs($args);
    }

    /**
     * Compare this DTO with another and return differences.
     *
     * @param  bool  $deep  Enable deep comparison for nested objects/arrays
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function diff(self $other, bool $deep = true): array
    {
        if (! $other instanceof static) {
            throw new InvalidArgumentException(
                'Can only diff DTOs of the same type. Expected '.static::class.', got '.$other::class,
            );
        }

        $thisArray = $this->toArray();
        $otherArray = $other->toArray();
        $differences = [];

        foreach ($thisArray as $key => $value) {
            if (! array_key_exists($key, $otherArray)) {
                $differences[$key] = ['old' => $value, 'new' => null];

                continue;
            }

            $otherValue = $otherArray[$key];

            if (! $this->valuesEqual($value, $otherValue, $deep)) {
                $differences[$key] = ['old' => $value, 'new' => $otherValue];
            }
        }

        foreach ($otherArray as $key => $value) {
            if (! array_key_exists($key, $thisArray)) {
                $differences[$key] = ['old' => null, 'new' => $value];
            }
        }

        return $differences;
    }

    /**
     * Check if two DTOs are equal (deep comparison by default).
     */
    public function equals(self $other): bool
    {
        return $other instanceof static && $this->diff($other, deep: true) === [];
    }

    /**
     * Generate hash for comparison/caching.
     */
    public function hash(): string
    {
        return hash('xxh3', serialize($this->toArray()));
    }

    /**
     * Merge this DTO with another DTO of the same type.
     * Values from $other override values from $this.
     */
    public function merge(self $other): static
    {
        if (! $other instanceof static) {
            throw new InvalidArgumentException(
                'Can only merge DTOs of the same type. Expected '.static::class.', got '.$other::class,
            );
        }

        $thisArray = $this->toArray();
        $otherArray = $other->toArray();

        return static::from(array_merge($thisArray, $otherArray));
    }

    /**
     * Deep merge - recursively merges nested arrays/DTOs.
     */
    public function mergeRecursive(self $other): static
    {
        if (! $other instanceof static) {
            throw new InvalidArgumentException(
                'Can only merge DTOs of the same type. Expected '.static::class.', got '.$other::class,
            );
        }

        $thisArray = $this->toArray();
        $otherArray = $other->toArray();

        return static::from(array_merge_recursive($thisArray, $otherArray));
    }

    /**
     * Create a deep clone of this DTO.
     */
    public function clone(): static
    {
        return static::from($this->toArray());
    }

    /**
     * Alias for clone() - Laravel-style naming.
     */
    public function replicate(): static
    {
        return $this->clone();
    }

    /**
     * Conditionally add properties to serialization.
     *
     * @param  array<string, mixed>|callable  $properties
     * @return array<string, mixed>
     */
    public function when(bool $condition, array|callable $properties): array
    {
        if (! $condition) {
            return [];
        }

        return is_callable($properties) ? $properties() : $properties;
    }

    /**
     * Inverse of when() - add properties unless condition is true.
     *
     * @param  array<string, mixed>|callable  $properties
     * @return array<string, mixed>
     */
    public function unless(bool $condition, array|callable $properties): array
    {
        return $this->when(! $condition, $properties);
    }

    /**
     * Hook called after successful hydration (instance context).
     * Override to add post-construction logic or cross-field validation.
     */
    protected function afterHydration(): void
    {
        // Override in subclasses
    }

    /**
     * Hook called before serialization (instance context).
     * Override to modify instance state before toArray().
     */
    protected function beforeSerialization(): void
    {
        // Override in subclasses
    }

    /**
     * Check equality between two values (with deep comparison support).
     */
    private function valuesEqual(mixed $a, mixed $b, bool $deep): bool
    {
        // Exact match (includes object identity)
        if ($a === $b) {
            return true;
        }

        if (! $deep) {
            return false;
        }

        // Deep comparison for arrays
        if (is_array($a) && is_array($b)) {
            if (count($a) !== count($b)) {
                return false;
            }

            foreach ($a as $key => $valueA) {
                if (! array_key_exists($key, $b)) {
                    return false;
                }

                if (! $this->valuesEqual($valueA, $b[$key], true)) {
                    return false;
                }
            }

            return true;
        }

        // Deep comparison for DTOs
        if ($a instanceof self && $b instanceof self) {
            return $a->equals($b);
        }

        // Loose equality for scalars
        return $a === $b;
    }
}
