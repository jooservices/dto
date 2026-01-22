<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use RuntimeException;

/**
 * Type-safe optional value wrapper.
 *
 * Represents a value that may or may not be present, providing type-safe access
 * and avoiding null pointer exceptions.
 *
 * @template T
 */
final readonly class Optional
{
    private function __construct(
        private mixed $value,
        private bool $present,
    ) {}

    /**
     * Create an Optional with a value.
     *
     * @template U
     *
     * @param  U  $value
     * @return Optional<U>
     */
    public static function of(mixed $value): self
    {
        return new self($value, true);
    }

    /**
     * Create an empty Optional.
     *
     * @template U
     *
     * @return Optional<U>
     */
    public static function empty(): self
    {
        return new self(null, false);
    }

    /**
     * Check if a value is present.
     */
    public function isPresent(): bool
    {
        return $this->present;
    }

    /**
     * Check if a value is absent.
     */
    public function isEmpty(): bool
    {
        return ! $this->present;
    }

    /**
     * Get the value if present, throw exception otherwise.
     *
     * @return T
     *
     * @throws RuntimeException If no value is present
     */
    public function get(): mixed
    {
        if (! $this->present) {
            throw new RuntimeException('Optional value not present');
        }

        return $this->value;
    }

    /**
     * Get the value if present, return default otherwise.
     *
     * @param  T  $default
     * @return T
     */
    public function orElse(mixed $default): mixed
    {
        return $this->present ? $this->value : $default;
    }

    /**
     * Get the value if present, call supplier function otherwise.
     *
     * @param  callable(): T  $supplier
     * @return T
     */
    public function orElseGet(callable $supplier): mixed
    {
        return $this->present ? $this->value : $supplier();
    }

    /**
     * Get the value if present, throw custom exception otherwise.
     *
     * @template E of \Throwable
     *
     * @param  callable(): E  $exceptionSupplier
     * @return T
     *
     * @throws E
     */
    public function orElseThrow(callable $exceptionSupplier): mixed
    {
        if (! $this->present) {
            throw $exceptionSupplier();
        }

        return $this->value;
    }

    /**
     * Execute callback if value is present.
     *
     * @param  callable(T): void  $consumer
     */
    public function ifPresent(callable $consumer): void
    {
        if ($this->present) {
            $consumer($this->value);
        }
    }

    /**
     * Execute callback if value is absent.
     */
    public function ifEmpty(callable $action): void
    {
        if (! $this->present) {
            $action();
        }
    }

    /**
     * Map the value if present.
     *
     * @template U
     *
     * @param  callable(T): U  $mapper
     * @return Optional<U>
     */
    public function map(callable $mapper): self
    {
        if (! $this->present) {
            return self::empty();
        }

        return self::of($mapper($this->value));
    }

    /**
     * Filter the value based on predicate.
     *
     * @param  callable(T): bool  $predicate
     * @return Optional<T>
     */
    public function filter(callable $predicate): self
    {
        if (! $this->present) {
            return $this;
        }

        return $predicate($this->value) ? $this : self::empty();
    }
}
