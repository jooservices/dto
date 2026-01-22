<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Collections;

use JOOservices\Dto\Collections\DataCollection;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\TestCase;

final class DataCollectionTest extends TestCase
{
    public function test_create_collection_from_array(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);

        $this->assertCount(2, $collection);
    }

    public function test_create_collection_from_already_hydrated_dtos(): void
    {
        $dto1 = SimpleDto::fromArray(['name' => 'John', 'age' => 30]);
        $dto2 = SimpleDto::fromArray(['name' => 'Jane', 'age' => 25]);

        $collection = new DataCollection(SimpleDto::class, [$dto1, $dto2]);

        $this->assertCount(2, $collection);
        $this->assertSame($dto1, $collection->first());
    }

    public function test_to_array(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $result = $collection->toArray();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertSame(['name' => 'John', 'age' => 30, 'email' => null], $result[0]);
        $this->assertSame(['name' => 'Jane', 'age' => 25, 'email' => null], $result[1]);
    }

    public function test_to_array_with_wrap(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $wrapped = $collection->wrap('users');
        $result = $wrapped->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertCount(2, $result['users']);
        $this->assertSame(['name' => 'John', 'age' => 30, 'email' => null], $result['users'][0]);
        $this->assertSame(['name' => 'Jane', 'age' => 25, 'email' => null], $result['users'][1]);
    }

    public function test_wrap_is_immutable(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $original = new DataCollection(SimpleDto::class, $items);
        $wrapped = $original->wrap('data');

        $this->assertNotSame($original, $wrapped);

        $originalResult = $original->toArray();
        $wrappedResult = $wrapped->toArray();

        $this->assertArrayNotHasKey('data', $originalResult);
        $this->assertArrayHasKey('data', $wrappedResult);
    }

    public function test_to_json(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $json = $collection->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertCount(2, $decoded);
    }

    public function test_to_json_with_wrap(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $json = $collection->wrap('users')->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('users', $decoded);
        $this->assertCount(1, $decoded['users']);
    }

    public function test_json_serialize(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $collection = new DataCollection(SimpleDto::class, $items);

        $serialized = $collection->jsonSerialize();

        $this->assertIsArray($serialized);
        $this->assertCount(1, $serialized);
    }

    public function test_count(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
            ['name' => 'Bob', 'age' => 35],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);

        $this->assertCount(3, $collection);
        $this->assertSame(3, $collection->count());
    }

    public function test_get_iterator(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);

        $names = [];

        foreach ($collection as $dto) {
            $this->assertInstanceOf(SimpleDto::class, $dto);
            $names[] = $dto->name;
        }

        $this->assertSame(['John', 'Jane'], $names);
    }

    public function test_all(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $all = $collection->all();

        $this->assertIsArray($all);
        $this->assertCount(2, $all);
        $this->assertContainsOnlyInstancesOf(SimpleDto::class, $all);
    }

    public function test_is_empty(): void
    {
        $empty = new DataCollection(SimpleDto::class, []);
        $notEmpty = new DataCollection(SimpleDto::class, [['name' => 'John', 'age' => 30]]);

        $this->assertTrue($empty->isEmpty());
        $this->assertFalse($notEmpty->isEmpty());
    }

    public function test_first(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $first = $collection->first();

        $this->assertInstanceOf(SimpleDto::class, $first);
        $this->assertSame(30, $first->age);
        $this->assertSame('John', $first->name);
    }

    public function test_first_returns_null_for_empty(): void
    {
        $collection = new DataCollection(SimpleDto::class, []);
        $first = $collection->first();

        $this->assertNull($first);
    }

    public function test_last(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $last = $collection->last();

        $this->assertInstanceOf(SimpleDto::class, $last);
        $this->assertSame(25, $last->age);
        $this->assertSame('Jane', $last->name);
    }

    public function test_last_returns_null_for_empty(): void
    {
        $collection = new DataCollection(SimpleDto::class, []);
        $last = $collection->last();

        $this->assertNull($last);
    }

    public function test_with_context(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30, 'email' => 'john@example.com'],
        ];

        $context = new Context(
            serializationOptions: new SerializationOptions(only: ['age', 'name']),
        );

        $collection = new DataCollection(SimpleDto::class, $items);
        $withContext = $collection->withContext($context);

        $this->assertNotSame($collection, $withContext);

        $result = $withContext->toArray();
        $this->assertArrayHasKey('age', $result[0]);
        $this->assertArrayHasKey('name', $result[0]);
    }

    public function test_preserves_array_keys(): void
    {
        $items = [
            'first' => ['name' => 'John', 'age' => 30],
            'second' => ['name' => 'Jane', 'age' => 25],
        ];

        $collection = new DataCollection(SimpleDto::class, $items);
        $result = $collection->toArray();

        $this->assertArrayHasKey('first', $result);
        $this->assertArrayHasKey('second', $result);
        $this->assertSame(['name' => 'John', 'age' => 30, 'email' => null], $result['first']);
        $this->assertSame(['name' => 'Jane', 'age' => 25, 'email' => null], $result['second']);
    }

    public function test_empty_collection(): void
    {
        $collection = new DataCollection(SimpleDto::class, []);

        $this->assertCount(0, $collection);
        $this->assertTrue($collection->isEmpty());
        $this->assertSame([], $collection->toArray());
        $this->assertNull($collection->first());
        $this->assertNull($collection->last());
    }

    public function test_collection_with_single_item(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $collection = new DataCollection(SimpleDto::class, $items);

        $this->assertCount(1, $collection);
        $this->assertFalse($collection->isEmpty());
        $this->assertSame($collection->first(), $collection->last());
    }

    public function test_multiple_wraps_are_immutable(): void
    {
        $items = [['name' => 'John', 'age' => 30]];
        $original = new DataCollection(SimpleDto::class, $items);
        $wrapped1 = $original->wrap('data');
        $wrapped2 = $wrapped1->wrap('users');

        $this->assertNotSame($original, $wrapped1);
        $this->assertNotSame($wrapped1, $wrapped2);

        $result1 = $wrapped1->toArray();
        $result2 = $wrapped2->toArray();

        $this->assertArrayHasKey('data', $result1);
        $this->assertArrayHasKey('users', $result2);
        $this->assertArrayNotHasKey('users', $result1);
    }
}
