<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Collections;

use InvalidArgumentException;
use JOOservices\Dto\Collections\PaginatedCollection;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AttrFixturePaginatorStub;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;
use stdClass;

final class PaginatedCollectionTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testExtractsItemsFromADuckTypedPaginatorObject(): void
    {
        $paginator = new AttrFixturePaginatorStub([
            ['name' => 'Ada', 'age' => 30],
            ['name' => 'Grace', 'age' => 40],
        ], total: 999);

        $collection = new PaginatedCollection(UserDto::class, $paginator, total: 2, perPage: 10, currentPage: 1);

        self::assertCount(2, $collection->items());
        self::assertSame(2, $collection->total());
        self::assertSame(10, $collection->perPage());
        self::assertSame(1, $collection->currentPage());
    }

    /**
     * Constructor pagination metadata is authoritative; the paginator's own total() is ignored.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPaginatorsOwnTotalMethodIsNeverConsulted(): void
    {
        $paginator = new AttrFixturePaginatorStub([['name' => 'Ada', 'age' => 30]], total: 999);

        $collection = new PaginatedCollection(UserDto::class, $paginator, total: 1);

        self::assertSame(1, $collection->total());
        self::assertNotSame(999, $collection->total());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsARawIterableInPlaceOfAPaginatorObject(): void
    {
        $collection = new PaginatedCollection(
            UserDto::class,
            [['name' => 'Ada', 'age' => 30]],
            total: 1,
        );

        self::assertCount(1, $collection->items());
    }

    /**
     * Unhappy: a paginator that duck-types neither items() nor iterable must fail loud.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMismatchedPaginatorRaisesHydrationException(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Paginator must expose items() or be iterable.');

        new PaginatedCollection(UserDto::class, new stdClass(), total: 5);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testToArrayShapeIncludesPaginationMetadata(): void
    {
        $paginator = new AttrFixturePaginatorStub([['name' => 'Ada', 'age' => 30]], total: 1);
        $collection = new PaginatedCollection(UserDto::class, $paginator, total: 1, perPage: 25, currentPage: 3);

        self::assertSame(
            [
                'data' => [['name' => 'Ada', 'age' => 30, 'email' => null]],
                'total' => 1,
                'perPage' => 25,
                'currentPage' => 3,
            ],
            $collection->toArray(),
        );
    }
}
