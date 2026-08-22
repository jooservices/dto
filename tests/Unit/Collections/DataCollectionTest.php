<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Collections;

use InvalidArgumentException;
use JOOservices\Dto\Collections\DataCollection;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class DataCollectionTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testHydratesArrayItemsIntoDtoInstances(): void
    {
        $collection = new DataCollection(UserDto::class, [
            ['name' => 'Ada', 'age' => 30],
            ['name' => 'Grace', 'age' => 40],
        ]);

        self::assertCount(2, $collection);
        $items = $collection->all();
        self::assertInstanceOf(UserDto::class, $items[0]);
        self::assertSame('Ada', $items[0]->name);
        self::assertSame('Grace', $items[1]->name);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsJsonStringItems(): void
    {
        $collection = new DataCollection(UserDto::class, [
            '{"name":"Ada","age":30}',
        ]);

        self::assertSame('Ada', $collection->all()[0]->name);
    }

    /**
     * Already-hydrated instances of the target class are kept as-is, not re-hydrated.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPreservesIdentityOfAlreadyHydratedDtoInstances(): void
    {
        $existing = new UserDto(name: 'Ada', age: 30);
        $collection = new DataCollection(UserDto::class, [$existing]);

        self::assertSame($existing, $collection->all()[0]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIteratesInInsertionOrder(): void
    {
        $collection = new DataCollection(UserDto::class, [
            'first' => ['name' => 'Ada', 'age' => 30],
            'second' => ['name' => 'Grace', 'age' => 40],
        ]);

        $names = [];
        foreach ($collection as $key => $dto) {
            $names[$key] = $dto->name;
        }

        self::assertSame(['first' => 'Ada', 'second' => 'Grace'], $names);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testJsonSerializeReturnsPlainArrayByDefault(): void
    {
        $collection = new DataCollection(UserDto::class, [
            ['name' => 'Ada', 'age' => 30],
        ]);

        self::assertSame([['name' => 'Ada', 'age' => 30, 'email' => null]], $collection->jsonSerialize());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testWrapWrapsSerializationUnderTheGivenKey(): void
    {
        $collection = new DataCollection(UserDto::class, [
            ['name' => 'Ada', 'age' => 30],
        ]);

        $wrapped = $collection->wrap('users');

        self::assertNotSame($collection, $wrapped);
        self::assertCount(1, $wrapped);
        self::assertSame(
            ['users' => [['name' => 'Ada', 'age' => 30, 'email' => null]]],
            $wrapped->jsonSerialize(),
        );
    }

    /**
     * Unhappy: a scalar item that is neither array, object, nor string is rejected outright.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNonHydratableItemRaisesHydrationException(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Collection item must be array, object, JSON string, or DTO.');

        new DataCollection(UserDto::class, [123]);
    }

    /**
     * Weird/edge: an object that is an instance of a *different* DTO subclass is not rejected
     * up front — DataCollection duck-types it through `$dtoClass::from()`, which extracts its
     * public properties and then fails because they don't satisfy the target constructor.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testItemFromAnUnrelatedDtoClassFailsMappingRatherThanBeingRejectedUpFront(): void
    {
        $this->expectException(MappingException::class);

        new DataCollection(UserDto::class, [new AddressDto(city: 'Hanoi')]);
    }
}
