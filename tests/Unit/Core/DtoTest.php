<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class DtoTest extends TestCase
{
    public function test_from_array(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = SimpleDto::fromArray([
            'name' => $name,
            'age' => $age,
        ]);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_from_json(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $json = json_encode(['name' => $name, 'age' => $age], JSON_THROW_ON_ERROR);
        $dto = SimpleDto::fromJson($json);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_from_object(): void
    {
        $obj = new stdClass;
        $obj->name = $this->faker->name();
        $obj->age = $this->faker->numberBetween(18, 99);

        $dto = SimpleDto::fromObject($obj);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($obj->name, $dto->name);
        $this->assertSame($obj->age, $dto->age);
    }

    public function test_from(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = SimpleDto::from(['name' => $name, 'age' => $age]);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_from_with_array_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $dto = SimpleDto::fromArray(['name' => $name, 'age' => $age], $context);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_from_with_json_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $json = json_encode(['name' => $name, 'age' => $age], JSON_THROW_ON_ERROR);
        $dto = SimpleDto::fromJson($json, $context);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_from_with_object_context(): void
    {
        $obj = new stdClass;
        $obj->name = $this->faker->name();
        $obj->age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $dto = SimpleDto::fromObject($obj, $context);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($obj->name, $dto->name);
        $this->assertSame($obj->age, $dto->age);
    }

    public function test_from_with_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $dto = SimpleDto::from(['name' => $name, 'age' => $age], $context);

        $this->assertInstanceOf(SimpleDto::class, $dto);
        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_to_array(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = new SimpleDto(name: $name, age: $age, email: null);
        $array = $dto->toArray();

        $this->assertIsArray($array);
        $this->assertSame($name, $array['name']);
        $this->assertSame($age, $array['age']);
    }

    public function test_to_array_with_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $email = $this->faker->email();

        $context = new Context(
            serializationOptions: new SerializationOptions(only: ['name', 'age']),
        );

        $dto = new SimpleDto(name: $name, age: $age, email: $email);
        $array = $dto->toArray($context);

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayNotHasKey('email', $array);
    }

    public function test_to_json(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = new SimpleDto(name: $name, age: $age, email: null);
        $json = $dto->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($name, $decoded['name']);
        $this->assertSame($age, $decoded['age']);
    }

    public function test_to_json_with_flags(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = new SimpleDto(name: $name, age: $age, email: null);
        $json = $dto->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
    }

    public function test_to_json_with_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $email = $this->faker->email();

        $context = new Context(
            serializationOptions: new SerializationOptions(except: ['email']),
        );

        $dto = new SimpleDto(name: $name, age: $age, email: $email);
        $json = $dto->toJson(0, $context);

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertArrayHasKey('age', $decoded);
        $this->assertArrayNotHasKey('email', $decoded);
    }

    public function test_json_serialize(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = new SimpleDto(name: $name, age: $age, email: null);
        $json = json_encode($dto, JSON_THROW_ON_ERROR);

        $this->assertIsString($json);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($name, $decoded['name']);
        $this->assertSame($age, $decoded['age']);
    }

    public function test_with(): void
    {
        $originalName = $this->faker->name();
        $originalAge = $this->faker->numberBetween(18, 99);
        $newAge = $this->faker->numberBetween(100, 150);

        $dto1 = new SimpleDto(name: $originalName, age: $originalAge, email: null);
        $dto2 = $dto1->with(['age' => $newAge]);

        $this->assertNotSame($dto1, $dto2);
        $this->assertSame($originalName, $dto2->name);
        $this->assertSame($newAge, $dto2->age);
        $this->assertSame($originalAge, $dto1->age);
    }

    public function test_with_preserves_other_properties(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $email = $this->faker->email();
        $newName = $this->faker->name();

        $dto1 = new SimpleDto(name: $name, age: $age, email: $email);
        $dto2 = $dto1->with(['name' => $newName]);

        $this->assertSame($newName, $dto2->name);
        $this->assertSame($age, $dto2->age);
        $this->assertSame($email, $dto2->email);
    }

    public function test_hidden_property_not_in_output(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'passwordHash' => 'secret',
        ]);

        $array = $dto->toArray();

        $this->assertArrayNotHasKey('passwordHash', $array);
    }
}
