<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core\Concerns;

use BadMethodCallException;
use OutOfBoundsException;

/**
 * Provides array access syntax for DTOs (read-only by default).
 *
 * This trait allows DTOs to be accessed using array syntax while maintaining
 * immutability. For mutable DTOs (Data class), offsetSet can be overridden.
 */
trait SupportsArrayAccess
{
    public function offsetExists(mixed $offset): bool
    {
        return property_exists($this, $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        if (! property_exists($this, $offset)) {
            throw new OutOfBoundsException(
                "Property '{$offset}' does not exist on ".static::class,
            );
        }

        return $this->{$offset};
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new BadMethodCallException(
            'DTOs are immutable. Use with() method instead.',
        );
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new BadMethodCallException(
            'DTOs are immutable. Properties cannot be unset.',
        );
    }
}
