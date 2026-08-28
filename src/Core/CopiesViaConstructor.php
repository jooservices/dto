<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use DateTimeInterface;
use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionException;

/**
 * Immutable copies via state view + constructor. Property-name keys only.
 */
abstract class CopiesViaConstructor extends HydratesFromInput
{
    /**
     * Accepts `$dto->with(['email' => $v])` or `$dto->with(email: $v)`.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function with(mixed ...$changes): static
    {
        $ctx = null;
        $lastKey = array_key_last($changes);
        if ($lastKey !== null && $changes[$lastKey] instanceof Context) {
            /** @var Context $ctx */
            $ctx = array_pop($changes);
        }

        $patch = $this->normalizeWithChanges($changes);

        /** @var static $instance */
        $instance = static::engine()->copyWith($this, $patch, $ctx);

        return $instance;
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function clone(): static
    {
        return $this->with();
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function replicate(): static
    {
        return $this->clone();
    }

    /**
     * @param  array<string, mixed>  $values
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function merge(array $values): static
    {
        return $this->with($values);
    }

    /**
     * @param  array<string, mixed>  $values
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function mergeRecursive(array $values): static
    {
        $merged = array_replace_recursive($this->stateView(), $values);

        /** @var array<string, mixed> $merged */
        return $this->with($merged);
    }

    /**
     * Property-keyed snapshot including hidden properties. No lazy compute.
     *
     * Nested DTOs become their state views, DateTimeInterface values become ATOM
     * strings, and arrays are walked recursively so equals/hash compare by value.
     *
     * @return array<string, mixed>
     */
    protected function stateView(): array
    {
        /** @var array<string, mixed> $state */
        $state = $this->canonicalizeStateValue(get_object_vars($this));

        return $state;
    }

    private function canonicalizeStateValue(mixed $value): mixed
    {
        if ($value instanceof self) {
            return $value->stateView();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format(DateTimeInterface::ATOM);
        }

        if (! is_array($value)) {
            return $value;
        }

        $canonical = [];
        foreach ($value as $key => $item) {
            $canonical[$key] = $this->canonicalizeStateValue($item);
        }

        return $canonical;
    }

    /**
     * @param  array<int<0, max>|string, mixed>  $changes
     * @return array<string, mixed>
     */
    private function normalizeWithChanges(array $changes): array
    {
        if ($changes === []) {
            return [];
        }

        if (count($changes) === 1 && isset($changes[0]) && is_array($changes[0])) {
            /** @var array<string, mixed> $patch */
            $patch = $changes[0];

            return $patch;
        }

        /** @var array<string, mixed> $changes */
        return $changes;
    }
}
