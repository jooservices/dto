<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core\Traits;

use JOOservices\Dto\Collections\DataCollection;
use JOOservices\Dto\Collections\PaginatedCollection;
use JOOservices\Dto\Core\Context;

trait CreatesCollections
{
    /**
     * Create a collection of DTOs from an iterable source.
     *
     * Automatically detects and handles Laravel paginators.
     *
     * @param  iterable<mixed>|object  $items  Iterable items or paginator instance
     * @param  Context|null  $context  Hydration and normalization context
     * @return DataCollection<static>|PaginatedCollection<static>
     *
     * @phpstan-return ($items is object ? PaginatedCollection<static> : DataCollection<static>)
     */
    public static function collection(iterable|object $items, ?Context $context = null): DataCollection|PaginatedCollection
    {
        // Detect Laravel paginator
        if (is_object($items) && method_exists($items, 'items') && method_exists($items, 'total')) {
            return PaginatedCollection::fromPaginator(static::class, $items, $context);
        }

        // Ensure items is iterable for DataCollection
        if (! is_iterable($items)) {
            $items = [];
        }

        return new DataCollection(static::class, $items, $context);
    }
}
