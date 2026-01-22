<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;

final class WrappingIntegrationTest extends TestCase
{
    public function test_single_dto_wrap(): void
    {
        $dto = SimpleDto::fromArray(['name' => 'John', 'age' => 30]);

        $context = new Context(
            serializationOptions: new SerializationOptions(wrap: 'data'),
        );

        $result = $dto->toArray($context);

        $this->assertArrayHasKey('data', $result);
        $this->assertSame(['name' => 'John', 'age' => 30, 'email' => null], $result['data']);
    }

    public function test_single_dto_wrap_with_context_wrap(): void
    {
        $dto = SimpleDto::fromArray(['name' => 'John', 'age' => 30]);

        $context = new Context()->wrap('user');

        $result = $dto->toArray($context);

        $this->assertArrayHasKey('user', $result);
        $this->assertSame(['name' => 'John', 'age' => 30, 'email' => null], $result['user']);
    }

    public function test_single_dto_wrap_with_filters(): void
    {
        $dto = SimpleDto::fromArray(['name' => 'John', 'age' => 30]);

        $context = new Context(
            serializationOptions: new SerializationOptions(
                only: ['name'],
                wrap: 'data',
            ),
        );

        $result = $dto->toArray($context);

        $this->assertArrayHasKey('data', $result);
        $this->assertSame(['name' => 'John'], $result['data']);
    }

    public function test_collection_with_wrap(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = SimpleDto::collection($items);
        $wrapped = $collection->wrap('users');
        $result = $wrapped->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertCount(2, $result['users']);
        $this->assertSame(['name' => 'John', 'age' => 30, 'email' => null], $result['users'][0]);
        $this->assertSame(['name' => 'Jane', 'age' => 25, 'email' => null], $result['users'][1]);
    }

    public function test_collection_with_context(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $context = new Context(
            serializationOptions: new SerializationOptions(only: ['name']),
        );

        $collection = SimpleDto::collection($items, $context);
        $result = $collection->toArray();

        $this->assertCount(2, $result);
        $this->assertSame(['name' => 'John'], $result[0]);
        $this->assertSame(['name' => 'Jane'], $result[1]);
    }

    public function test_collection_with_wrap_and_context(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $context = new Context(
            serializationOptions: new SerializationOptions(only: ['age']),
        );

        $collection = SimpleDto::collection($items, $context)->wrap('users');
        $result = $collection->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertCount(2, $result['users']);
        $this->assertSame(['age' => 30], $result['users'][0]);
        $this->assertSame(['age' => 25], $result['users'][1]);
    }

    public function test_nested_dto_with_wrap(): void
    {
        $data = [
            'id' => '123',
            'name' => 'John Doe',
            'email_address' => 'john@example.com',
            'createdAt' => '2024-01-01T00:00:00+00:00',
            'address' => [
                'street' => '123 Main St',
                'city' => 'New York',
                'country' => 'USA',
            ],
        ];

        $dto = UserDto::fromArray($data);

        $context = new Context()->wrap('user');

        $result = $dto->toArray($context);

        $this->assertArrayHasKey('user', $result);
        $this->assertSame('123', $result['user']['id']);
        $this->assertSame('John Doe', $result['user']['name']);
    }

    public function test_collection_to_json(): void
    {
        $items = [
            ['name' => 'John', 'age' => 30],
            ['name' => 'Jane', 'age' => 25],
        ];

        $collection = SimpleDto::collection($items)->wrap('users');
        $json = $collection->toJson();

        $this->assertJson($json);

        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('users', $decoded);
        $this->assertCount(2, $decoded['users']);
    }

    public function test_collection_json_serialize(): void
    {
        $items = [['name' => 'John', 'age' => 30]];

        $collection = SimpleDto::collection($items)->wrap('data');
        $encoded = json_encode($collection);

        $this->assertJson($encoded);

        $decoded = json_decode($encoded, true);
        $this->assertArrayHasKey('data', $decoded);
    }

    public function test_wrap_without_key(): void
    {
        $dto = SimpleDto::fromArray(['name' => 'John', 'age' => 30]);

        $context = new Context(
            serializationOptions: new SerializationOptions(wrap: null),
        );

        $result = $dto->toArray($context);

        // No wrapping, direct array
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertSame(30, $result['age']);
        $this->assertSame('John', $result['name']);
    }

    public function test_changing_wrap_key(): void
    {
        $items = [['name' => 'John', 'age' => 30]];

        $collection = SimpleDto::collection($items);

        $wrappedData = $collection->wrap('data')->toArray();
        $wrappedUsers = $collection->wrap('users')->toArray();

        $this->assertArrayHasKey('data', $wrappedData);
        $this->assertArrayHasKey('users', $wrappedUsers);
        $this->assertArrayNotHasKey('users', $wrappedData);
        $this->assertArrayNotHasKey('data', $wrappedUsers);
    }

    public function test_collection_preserves_keys(): void
    {
        $items = [
            'first' => ['name' => 'John', 'age' => 30],
            'second' => ['name' => 'Jane', 'age' => 25],
        ];

        $collection = SimpleDto::collection($items)->wrap('users');
        $result = $collection->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertArrayHasKey('first', $result['users']);
        $this->assertArrayHasKey('second', $result['users']);
    }

    public function test_empty_collection_with_wrap(): void
    {
        $collection = SimpleDto::collection([])->wrap('users');
        $result = $collection->toArray();

        $this->assertArrayHasKey('users', $result);
        $this->assertSame([], $result['users']);
    }

    public function test_multiple_context_transformations(): void
    {
        $dto = SimpleDto::fromArray(['name' => 'John', 'age' => 30]);

        // First transformation: filter
        $context1 = new Context(
            serializationOptions: new SerializationOptions(only: ['name']),
        );

        // Second transformation: wrap
        $context2 = $context1->wrap('user');

        $result = $dto->toArray($context2);

        $this->assertArrayHasKey('user', $result);
        $this->assertSame(['name' => 'John'], $result['user']);
    }

    public function test_context_immutability(): void
    {
        $original = new Context;
        $wrapped = $original->wrap('data');

        $this->assertNotSame($original, $wrapped);
        $this->assertNull($original->getSerializationOptions()->wrap);
        $this->assertSame('data', $wrapped->getSerializationOptions()->wrap);
    }
}
