<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use RuntimeException;
use Throwable;

/**
 * Helper wrapper for present/absent values (not presence-tracking in hydration until a later release).
 *
 * @template T
 */
final readonly class Optional
{
    /**
     * @param  T|null  $value
     */
    private function __construct(
        private mixed $value,
        private bool $present,
    ) {
    }

    /**
     * @template TValue
     *
     * @param  TValue  $value
     * @return self<TValue>
     */
    public static function fromValue(mixed $value): self
    {
        return new self($value, true);
    }

    /**
     * @return self<mixed>
     */
    public static function empty(): self
    {
        /** @var self<mixed> $optional */
        $optional = new self(null, false);

        return $optional;
    }

    public function isPresent(): bool
    {
        return $this->present;
    }

    public function isEmpty(): bool
    {
        return ! $this->present;
    }

    /**
     * @return T
     *
     * @throws RuntimeException
     */
    public function get(): mixed
    {
        if (! $this->present) {
            throw new RuntimeException('Optional value is empty.');
        }

        /** @var T $value */
        $value = $this->value;

        return $value;
    }

    /**
     * @template TDefault
     *
     * @param  TDefault  $default
     * @return T|TDefault
     */
    public function orElse(mixed $default): mixed
    {
        if (! $this->present) {
            return $default;
        }

        /** @var T $value */
        $value = $this->value;

        return $value;
    }

    /**
     * @param  callable():mixed  $supplier
     */
    public function orElseGet(callable $supplier): mixed
    {
        if (! $this->present) {
            return $supplier();
        }

        /** @var T $value */
        $value = $this->value;

        return $value;
    }

    /**
     * @param  callable():Throwable  $exceptionSupplier
     */
    public function orElseThrow(callable $exceptionSupplier): mixed
    {
        if ($this->present) {
            return $this->value;
        }

        throw $exceptionSupplier();
    }

    /**
     * @param  callable(T):void  $consumer
     */
    public function ifPresent(callable $consumer): void
    {
        if ($this->present) {
            /** @var T $value */
            $value = $this->value;
            $consumer($value);

            return;
        }
    }

    /**
     * @param  callable():void  $action
     */
    public function ifEmpty(callable $action): void
    {
        if (! $this->present) {
            $action();
        }
    }

    /**
     * @param  callable(T):mixed  $mapper
     * @return self<mixed>
     */
    public function map(callable $mapper): self
    {
        if (! $this->present) {
            return self::empty();
        }

        /** @var T $value */
        $value = $this->value;

        return self::fromValue($mapper($value));
    }

    /**
     * @param  callable(T):bool  $predicate
     * @return self<T>
     */
    public function filter(callable $predicate): self
    {
        if (! $this->present) {
            /** @var self<T> $filtered */
            $filtered = self::empty();

            return $filtered;
        }

        /** @var T $value */
        $value = $this->value;

        if (! $predicate($value)) {
            /** @var self<T> $filtered */
            $filtered = self::empty();

            return $filtered;
        }

        return $this;
    }
}
