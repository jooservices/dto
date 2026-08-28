<?php

declare(strict_types=1);

namespace JOOservices\Dto\Collections;

use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JsonSerializable;
use ReflectionException;
use Traversable;

/**
 * Hydrates every item eagerly in the constructor. For very large datasets,
 * hydrate items lazily or in batches instead of passing the full iterable here.
 *
 * @template T of AbstractDto
 *
 * @implements IteratorAggregate<int|string, T>
 */
final class DataCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<int|string, T> */
    private array $items;

    /**
     * @param  class-string<T>  $dtoClass
     * @param  iterable<int|string, mixed>  $items
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function __construct(
        private readonly string $dtoClass,
        iterable $items,
        private readonly ?Context $context = null,
        private readonly ?string $wrapKey = null,
    ) {
        $hydrated = [];
        foreach ($items as $key => $item) {
            $offset = $key;
            if ($item instanceof $dtoClass) {
                $hydrated[$offset] = $item;
                continue;
            }

            if (! is_array($item) && ! is_object($item) && ! is_string($item)) {
                throw new HydrationException(
                    message: 'Collection item must be array, object, JSON string, or DTO.',
                    givenType: get_debug_type($item),
                );
            }

            /** @var array<string, mixed>|object|string $item */
            $hydrated[$offset] = $dtoClass::from($item, $this->context);
        }

        $this->items = $hydrated;
    }

    /**
     * @return self<T>
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function wrap(string $key): self
    {
        return new self($this->dtoClass, $this->items, $this->context, $key);
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return array<int|string, mixed>|array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function jsonSerialize(): mixed
    {
        $rows = [];
        foreach ($this->items as $key => $item) {
            $rows[$key] = $item->toArray($this->context);
        }

        if ($this->wrapKey !== null) {
            return [$this->wrapKey => $rows];
        }

        return $rows;
    }

    /**
     * @return array<int|string, mixed>|array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        /** @var array<int|string, mixed>|array<string, mixed> $rows */
        $rows = $this->jsonSerialize();

        return $rows;
    }

    /**
     * @return array<int|string, T>
     */
    public function all(): array
    {
        return $this->items;
    }
}
