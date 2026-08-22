<?php

declare(strict_types=1);

namespace JOOservices\Dto\Collections;

use InvalidArgumentException;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionException;

/**
 * Paginator wrapper. Reads item rows from a duck-typed {@see items()} source or a raw iterable.
 * Pagination metadata ({@see total()}, {@see perPage()}, {@see currentPage()}) always comes from
 * the constructor arguments, not from the paginator object.
 *
 * @template T of AbstractDto
 */
final class PaginatedCollection
{
    /** @var DataCollection<T> */
    private DataCollection $collection;

    /**
     * @param  class-string<T>  $dtoClass
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function __construct(
        string $dtoClass,
        mixed $paginator,
        private readonly int $total,
        private readonly int $perPage = 15,
        private readonly int $currentPage = 1,
        ?Context $context = null,
    ) {
        $items = $this->extractItems($paginator);
        $this->collection = new DataCollection($dtoClass, $items, $context);
    }

    /**
     * @return DataCollection<T>
     */
    public function items(): DataCollection
    {
        return $this->collection;
    }

    public function total(): int
    {
        return $this->total;
    }

    public function perPage(): int
    {
        return $this->perPage;
    }

    public function currentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function toArray(): array
    {
        return [
            'data' => $this->collection->jsonSerialize(),
            'total' => $this->total,
            'perPage' => $this->perPage,
            'currentPage' => $this->currentPage,
        ];
    }

    /**
     * @return iterable<int|string, mixed>
     *
     * @throws HydrationException
     */
    private function extractItems(mixed $paginator): iterable
    {
        if (is_object($paginator) && method_exists($paginator, 'items')) {
            /** @var iterable<int|string, mixed> $items */
            $items = $paginator->items();

            return $items;
        }

        if (is_iterable($paginator)) {
            /** @var iterable<int|string, mixed> $paginator */

            return $paginator;
        }

        throw new HydrationException(
            message: 'Paginator must expose items() or be iterable.',
            givenType: is_object($paginator) ? $paginator::class : get_debug_type($paginator),
        );
    }
}
