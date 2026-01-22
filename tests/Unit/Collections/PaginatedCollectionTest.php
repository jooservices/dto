<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Collections;

use JOOservices\Dto\Collections\PaginatedCollection;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\TestCase;

final class PaginatedCollectionTest extends TestCase
{
    public function test_create_paginated_collection(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $meta = [
            'current_page' => 1,
            'per_page' => 15,
            'total' => 100,
            'last_page' => 7,
        ];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);

        $this->assertCount(2, $collection);
    }

    public function test_to_array_includes_metadata(): void
    {
        $items = [['name' => 'John', 'age' => 30]];

        $meta = [
            'current_page' => 1,
            'per_page' => 15,
            'total' => 100,
            'last_page' => 7,
        ];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $result = $collection->toArray();

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
        $this->assertSame($meta, $result['meta']);
    }

    public function test_to_array_with_links(): void
    {
        $items = [['name' => 'John', 'age' => 30]];

        $meta = [
            'current_page' => 2,
            'per_page' => 15,
            'total' => 100,
            'last_page' => 7,
        ];

        $links = [
            'first' => 'http://example.com?page=1',
            'last' => 'http://example.com?page=7',
            'prev' => 'http://example.com?page=1',
            'next' => 'http://example.com?page=3',
        ];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta, $links);
        $result = $collection->toArray();

        $this->assertArrayHasKey('links', $result);
        $this->assertSame($links, $result['links']);
    }

    public function test_to_array_without_links(): void
    {
        $items = [['name' => 'John', 'age' => 30]];

        $meta = ['current_page' => 1, 'per_page' => 15];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $result = $collection->toArray();

        $this->assertArrayNotHasKey('links', $result);
    }

    public function test_wrap_changes_data_key(): void
    {
        $items = [['name' => 'John', 'age' => 30]];

        $meta = ['current_page' => 1, 'per_page' => 15];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $wrapped = $collection->wrap('users');
        $result = $wrapped->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertArrayNotHasKey('data', $result);
        $this->assertArrayHasKey('meta', $result);
    }

    public function test_wrap_is_immutable(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $meta = ['current_page' => 1];

        $original = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $wrapped = $original->wrap('users');

        $this->assertNotSame($original, $wrapped);

        $originalResult = $original->toArray();
        $wrappedResult = $wrapped->toArray();

        $this->assertArrayHasKey('data', $originalResult);
        $this->assertArrayHasKey('users', $wrappedResult);
    }

    public function test_get_meta(): void
    {
        $meta = [
            'current_page' => 2,
            'per_page' => 25,
            'total' => 150,
        ];

        $collection = new PaginatedCollection(SimpleDto::class, [], $meta);

        $this->assertSame($meta, $collection->getMeta());
    }

    public function test_get_links(): void
    {
        $links = [
            'first' => 'http://example.com?page=1',
            'last' => 'http://example.com?page=10',
        ];

        $collection = new PaginatedCollection(
            SimpleDto::class,
            [],
            ['current_page' => 1],
            $links,
        );

        $this->assertSame($links, $collection->getLinks());
    }

    public function test_get_links_returns_null(): void
    {
        $collection = new PaginatedCollection(SimpleDto::class, [], ['current_page' => 1]);

        $this->assertNull($collection->getLinks());
    }

    public function test_get_current_page(): void
    {
        $meta = ['current_page' => 3];
        $collection = new PaginatedCollection(SimpleDto::class, [], $meta);

        $this->assertSame(3, $collection->getCurrentPage());
    }

    public function test_get_current_page_returns_null(): void
    {
        $collection = new PaginatedCollection(SimpleDto::class, [], []);

        $this->assertNull($collection->getCurrentPage());
    }

    public function test_get_per_page(): void
    {
        $meta = ['per_page' => 50];
        $collection = new PaginatedCollection(SimpleDto::class, [], $meta);

        $this->assertSame(50, $collection->getPerPage());
    }

    public function test_get_per_page_returns_null(): void
    {
        $collection = new PaginatedCollection(SimpleDto::class, [], []);

        $this->assertNull($collection->getPerPage());
    }

    public function test_get_total(): void
    {
        $meta = ['total' => 250];
        $collection = new PaginatedCollection(SimpleDto::class, [], $meta);

        $this->assertSame(250, $collection->getTotal());
    }

    public function test_get_total_returns_null(): void
    {
        $collection = new PaginatedCollection(SimpleDto::class, [], []);

        $this->assertNull($collection->getTotal());
    }

    public function test_get_last_page(): void
    {
        $meta = ['last_page' => 10];
        $collection = new PaginatedCollection(SimpleDto::class, [], $meta);

        $this->assertSame(10, $collection->getLastPage());
    }

    public function test_get_last_page_returns_null(): void
    {
        $collection = new PaginatedCollection(SimpleDto::class, [], []);

        $this->assertNull($collection->getLastPage());
    }

    public function test_from_paginator(): void
    {
        $paginator = $this->createMockPaginator([
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ]);

        $collection = PaginatedCollection::fromPaginator(SimpleDto::class, $paginator);

        $this->assertInstanceOf(PaginatedCollection::class, $collection);
        $this->assertCount(2, $collection);
        $this->assertSame(1, $collection->getCurrentPage());
        $this->assertSame(15, $collection->getPerPage());
        $this->assertSame(100, $collection->getTotal());
        $this->assertSame(7, $collection->getLastPage());
    }

    public function test_from_paginator_with_links(): void
    {
        $paginator = $this->createMockPaginator([['name' => 'John', 'age' => 30]]);

        $collection = PaginatedCollection::fromPaginator(SimpleDto::class, $paginator);
        $links = $collection->getLinks();

        $this->assertNotNull($links);
        $this->assertArrayHasKey('first', $links);
        $this->assertArrayHasKey('last', $links);
        $this->assertArrayHasKey('prev', $links);
        $this->assertArrayHasKey('next', $links);
    }

    public function test_from_paginator_with_wrap(): void
    {
        $paginator = $this->createMockPaginator([['name' => 'John', 'age' => 30]]);

        $collection = PaginatedCollection::fromPaginator(
            SimpleDto::class,
            $paginator,
            wrapKey: 'users',
        );

        $result = $collection->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertArrayNotHasKey('data', $result);
    }

    public function test_with_context(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $meta = ['current_page' => 1];

        $context = new Context;
        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $withContext = $collection->withContext($context);

        $this->assertNotSame($collection, $withContext);
        $this->assertInstanceOf(PaginatedCollection::class, $withContext);
    }

    public function test_inherits_data_collection_behavior(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];
        $meta = ['current_page' => 1];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);

        // Test inherited methods
        $this->assertCount(2, $collection);
        $this->assertFalse($collection->isEmpty());
        $this->assertInstanceOf(SimpleDto::class, $collection->first());
        $this->assertInstanceOf(SimpleDto::class, $collection->last());

        $all = $collection->all();
        $this->assertCount(2, $all);
    }

    public function test_json_serialize(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $meta = ['current_page' => 1, 'total' => 10];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $serialized = $collection->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertArrayHasKey('data', $serialized);
        $this->assertArrayHasKey('meta', $serialized);
    }

    public function test_to_json(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $meta = ['current_page' => 1];

        $collection = new PaginatedCollection(SimpleDto::class, $items, $meta);
        $json = $collection->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('data', $decoded);
        $this->assertArrayHasKey('meta', $decoded);
    }

    /**
     * Create a mock paginator for testing.
     *
     * @param  array<mixed>  $items
     */
    private function createMockPaginator(array $items): object
    {
        return new class($items)
        {
            public function __construct(private array $items) {}

            public function items(): array
            {
                return $this->items;
            }

            public function currentPage(): int
            {
                return 1;
            }

            public function perPage(): int
            {
                return 15;
            }

            public function total(): int
            {
                return 100;
            }

            public function lastPage(): int
            {
                return 7;
            }

            public function url(int $page): string
            {
                return "http://example.com?page={$page}";
            }

            public function previousPageUrl(): ?string
            {
                return null;
            }

            public function nextPageUrl(): ?string
            {
                return 'http://example.com?page=2';
            }
        };
    }
}
