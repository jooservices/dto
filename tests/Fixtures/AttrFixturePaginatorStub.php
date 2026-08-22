<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

/**
 * Duck-typed paginator stub exposing {@see items()} and an unused {@see total()} helper.
 *
 * @see \JOOservices\Dto\Collections\PaginatedCollection
 */
final class AttrFixturePaginatorStub
{
    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function __construct(
        private readonly array $rows,
        private readonly int $total,
    ) {
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function items(): array
    {
        return $this->rows;
    }

    public function total(): int
    {
        return $this->total;
    }
}
