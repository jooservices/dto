<?php

declare(strict_types=1);

namespace JOOservices\Dto\Collections;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Engine\EngineInterface;
use ReflectionClass;

/**
 * Immutable paginated collection for DTOs.
 *
 * Supports pagination metadata typically from Laravel paginators.
 *
 * @template T of \JOOservices\Dto\Core\Dto
 *
 * @extends DataCollection<T>
 */
readonly class PaginatedCollection extends DataCollection
{
    /**
     * @param  class-string<T>  $dtoClass  Fully qualified DTO class name
     * @param  iterable<mixed>  $items  Items to hydrate into DTOs
     * @param  array<string, mixed>  $meta  Pagination metadata
     * @param  array<string, mixed>|null  $links  Pagination links (optional)
     * @param  Context|null  $context  Hydration context
     * @param  string|null  $wrapKey  Optional key to wrap the collection data
     * @param  EngineInterface|null  $engine  Optional engine to use
     */
    public function __construct(
        string $dtoClass,
        iterable $items,
        private array $meta,
        private ?array $links = null,
        ?Context $context = null,
        ?string $wrapKey = null,
        ?EngineInterface $engine = null,
    ) {
        parent::__construct($dtoClass, $items, $context, $wrapKey, $engine);
    }

    /**
     * Create from Laravel LengthAwarePaginator or similar paginator.
     *
     * @template TPaginated of \JOOservices\Dto\Core\Dto
     *
     * @param  class-string<TPaginated>  $dtoClass  Fully qualified DTO class name
     * @param  object  $paginator  Paginator instance with items() and pagination methods
     * @param  Context|null  $context  Hydration context
     * @param  string|null  $wrapKey  Optional key to wrap the collection data
     * @return self<TPaginated>
     */
    public static function fromPaginator(
        string $dtoClass,
        object $paginator,
        ?Context $context = null,
        ?string $wrapKey = null,
    ): self {
        // Extract items using common paginator methods
        $items = method_exists($paginator, 'items') ? $paginator->items() : [];

        // Ensure items is iterable
        if (! is_iterable($items)) {
            $items = [];
        }

        // Build metadata from common paginator properties/methods
        $meta = [];

        if (method_exists($paginator, 'currentPage')) {
            $meta['current_page'] = $paginator->currentPage();
        }

        if (method_exists($paginator, 'perPage')) {
            $meta['per_page'] = $paginator->perPage();
        }

        if (method_exists($paginator, 'total')) {
            $meta['total'] = $paginator->total();
        }

        if (method_exists($paginator, 'lastPage')) {
            $meta['last_page'] = $paginator->lastPage();
        }

        // Build links if methods are available
        $links = null;

        if (method_exists($paginator, 'url') && isset($meta['last_page'])) {
            $links = [
                'first' => $paginator->url(1),
                'last' => $paginator->url($meta['last_page']),
                'prev' => method_exists($paginator, 'previousPageUrl') ? $paginator->previousPageUrl() : null,
                'next' => method_exists($paginator, 'nextPageUrl') ? $paginator->nextPageUrl() : null,
            ];
        }

        return new self($dtoClass, $items, $meta, $links, $context, $wrapKey);
    }

    /**
     * Create new paginated collection with wrap key.
     *
     * @param  string  $key  The key to wrap collection data
     * @return self<T> New immutable instance with wrap configuration
     */
    public function wrap(string $key): self
    {
        /** @var class-string<T> $dtoClass */
        $dtoClass = $this->getDtoClass();

        return new self(
            $dtoClass,
            parent::all(),
            $this->meta,
            $this->links,
            $this->getContext(),
            $key,
            $this->getEngineInstance(),
        );
    }

    /**
     * Create new paginated collection with context.
     *
     * @param  Context  $context  The context to use for normalization
     * @return self<T> New immutable instance with context
     */
    public function withContext(Context $context): self
    {
        /** @var class-string<T> $dtoClass */
        $dtoClass = $this->getDtoClass();

        return new self(
            $dtoClass,
            parent::all(),
            $this->meta,
            $this->links,
            $context,
            $this->getWrapKey(),
            $this->getEngineInstance(),
        );
    }

    /**
     * Convert paginated collection to array with metadata and links.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        // If parent already wrapped (wrapKey set), extract the data
        $wrapKey = $this->getWrapKey();

        if ($wrapKey !== null && isset($data[$wrapKey])) {
            $items = $data[$wrapKey];
        } else {
            $items = $data;
        }

        $result = [
            $wrapKey ?? 'data' => $items,
            'meta' => $this->meta,
        ];

        if ($this->links !== null) {
            $result['links'] = $this->links;
        }

        return $result;
    }

    /**
     * Get pagination metadata.
     *
     * @return array<string, mixed>
     */
    public function getMeta(): array
    {
        return $this->meta;
    }

    /**
     * Get pagination links.
     *
     * @return array<string, mixed>|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Get current page from metadata.
     */
    public function getCurrentPage(): ?int
    {
        $value = $this->meta['current_page'] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * Get per page from metadata.
     */
    public function getPerPage(): ?int
    {
        $value = $this->meta['per_page'] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * Get total items from metadata.
     */
    public function getTotal(): ?int
    {
        $value = $this->meta['total'] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * Get last page from metadata.
     */
    public function getLastPage(): ?int
    {
        $value = $this->meta['last_page'] ?? null;

        return is_int($value) ? $value : null;
    }

    /**
     * Get DTO class name (for child class overrides).
     *
     * @return class-string<\JOOservices\Dto\Core\Dto>
     */
    private function getDtoClass(): string
    {
        // Use reflection to access parent's private property
        $reflection = new ReflectionClass(parent::class);
        $property = $reflection->getProperty('dtoClass');

        /** @var class-string<\JOOservices\Dto\Core\Dto> $value */
        $value = $property->getValue($this);

        return $value;
    }

    /**
     * Get context (for child class overrides).
     */
    private function getContext(): ?Context
    {
        // Use reflection to access parent's private property
        $reflection = new ReflectionClass(parent::class);
        $property = $reflection->getProperty('context');

        /** @var Context|null $value */
        $value = $property->getValue($this);

        return $value;
    }

    /**
     * Get wrap key (for child class overrides).
     */
    private function getWrapKey(): ?string
    {
        // Use reflection to access parent's private property
        $reflection = new ReflectionClass(parent::class);
        $property = $reflection->getProperty('wrapKey');

        /** @var string|null $value */
        $value = $property->getValue($this);

        return $value;
    }
}
