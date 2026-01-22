<?php

declare(strict_types=1);

namespace JOOservices\Dto\Collections;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Engine\EngineFactory;
use JOOservices\Dto\Engine\EngineInterface;
use JsonSerializable;
use Traversable;

/**
 * Immutable collection for DTOs.
 *
 * @template T of Dto
 *
 * @implements IteratorAggregate<int|string, T>
 */
readonly class DataCollection implements Countable, IteratorAggregate, JsonSerializable
{
    /** @var array<int|string, T> */
    private array $items;

    /**
     * @param  class-string<T>  $dtoClass  Fully qualified DTO class name
     * @param  iterable<mixed>  $items  Items to hydrate into DTOs
     * @param  Context|null  $context  Hydration context
     * @param  string|null  $wrapKey  Optional key to wrap the collection output
     * @param  EngineInterface|null  $engine  Optional engine to use (defaults to fresh EngineFactory)
     */
    public function __construct(
        private string $dtoClass,
        iterable $items,
        private ?Context $context = null,
        private ?string $wrapKey = null,
        private ?EngineInterface $engine = null,
    ) {
        $this->items = $this->hydrateItems($items);
    }

    /**
     * Create new collection with wrap key.
     *
     * @param  string  $key  The key to wrap collection output (e.g., 'users', 'data')
     * @return self<T> New immutable instance with wrap configuration
     */
    public function wrap(string $key): self
    {
        return new self(
            $this->dtoClass,
            $this->items,
            $this->context,
            $key,
            $this->engine,
        );
    }

    /**
     * Create new collection with context.
     *
     * @param  Context  $context  The context to use for normalization
     * @return self<T> New immutable instance with context
     */
    public function withContext(Context $context): self
    {
        return new self(
            $this->dtoClass,
            $this->items,
            $context,
            $this->wrapKey,
            $this->engine,
        );
    }

    /**
     * Create new collection with a specific engine.
     *
     * @param  EngineInterface  $engine  The engine to use for hydration/normalization
     * @return self<T> New immutable instance with engine
     */
    public function withEngine(EngineInterface $engine): self
    {
        return new self(
            $this->dtoClass,
            $this->items,
            $this->context,
            $this->wrapKey,
            $engine,
        );
    }

    /**
     * Convert collection to array.
     *
     * @return array<int|string, array<string, mixed>>|array<string, mixed>
     */
    public function toArray(): array
    {
        $engine = $this->getEngine();
        $result = [];

        foreach ($this->items as $key => $item) {
            $result[$key] = $engine->normalize($item, $this->context);
        }

        if ($this->wrapKey !== null) {
            return [$this->wrapKey => $result];
        }

        return $result;
    }

    /**
     * Convert collection to JSON.
     *
     * @param  int  $flags  JSON encoding flags
     * @return string JSON representation
     */
    public function toJson(int $flags = 0): string
    {
        $json = json_encode($this->toArray(), $flags);

        return $json !== false ? $json : '[]';
    }

    /**
     * @return array<int|string, array<string, mixed>>|array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int|string, T>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Get all items in the collection.
     *
     * @return array<int|string, T>
     */
    public function all(): array
    {
        return $this->items;
    }

    /**
     * Check if collection is empty.
     */
    public function isEmpty(): bool
    {
        return count($this->items) === 0;
    }

    /**
     * Get first item or null if empty.
     *
     * @return T|null
     */
    public function first(): ?Dto
    {
        foreach ($this->items as $item) {
            return $item;
        }

        return null;
    }

    /**
     * Get last item or null if empty.
     *
     * @return T|null
     */
    public function last(): ?Dto
    {
        if (count($this->items) === 0) {
            return null;
        }

        $items = $this->items;
        $last = end($items);

        return $last !== false ? $last : null;
    }

    /**
     * Get the engine instance for subclass access.
     */
    protected function getEngineInstance(): ?EngineInterface
    {
        return $this->engine;
    }

    /**
     * Hydrate items into DTOs.
     *
     * @param  iterable<mixed>  $items
     * @return array<int|string, T>
     */
    private function hydrateItems(iterable $items): array
    {
        $engine = $this->getEngine();

        /** @var array<int|string, T> $result */
        $result = [];

        foreach ($items as $key => $item) {
            /** @var int|string $key */
            // If already a DTO instance of the correct type, use it directly
            if ($item instanceof $this->dtoClass) {
                /** @var T $item */
                $result[$key] = $item;
            } else {
                // Otherwise hydrate it
                /** @var class-string<T> $dtoClass */
                $dtoClass = $this->dtoClass;

                /** @var T $hydrated */
                $hydrated = $engine->hydrate($dtoClass, $item, $this->context);
                $result[$key] = $hydrated;
            }
        }

        return $result;
    }

    private function getEngine(): EngineInterface
    {
        return $this->engine ?? new EngineFactory()->create();
    }
}
