<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\Fixtures\SimpleData;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class DataTest extends TestCase
{
    public function test_from_array(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = SimpleData::fromArray([
            'name' => $name,
            'age' => $age,
        ]);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_from_json(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $json = json_encode(['name' => $name, 'age' => $age], JSON_THROW_ON_ERROR);
        $data = SimpleData::fromJson($json);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_from_object(): void
    {
        $obj = new stdClass;
        $obj->name = $this->faker->name();
        $obj->age = $this->faker->numberBetween(18, 99);

        $data = SimpleData::fromObject($obj);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($obj->name, $data->name);
        $this->assertSame($obj->age, $data->age);
    }

    public function test_from(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = SimpleData::from(['name' => $name, 'age' => $age]);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_from_with_array_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $data = SimpleData::fromArray(['name' => $name, 'age' => $age], $context);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_from_with_json_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $json = json_encode(['name' => $name, 'age' => $age], JSON_THROW_ON_ERROR);
        $data = SimpleData::fromJson($json, $context);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_from_with_object_context(): void
    {
        $obj = new stdClass;
        $obj->name = $this->faker->name();
        $obj->age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $data = SimpleData::fromObject($obj, $context);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($obj->name, $data->name);
        $this->assertSame($obj->age, $data->age);
    }

    public function test_from_with_context(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $context = new Context;

        $data = SimpleData::from(['name' => $name, 'age' => $age], $context);

        $this->assertInstanceOf(SimpleData::class, $data);
        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_to_array(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = new SimpleData(name: $name, age: $age, email: null);
        $array = $data->toArray();

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

        $data = new SimpleData(name: $name, age: $age, email: $email);
        $array = $data->toArray($context);

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayNotHasKey('email', $array);
    }

    public function test_to_json(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = new SimpleData(name: $name, age: $age, email: null);
        $json = $data->toJson();

        $this->assertIsString($json);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($name, $decoded['name']);
        $this->assertSame($age, $decoded['age']);
    }

    public function test_to_json_with_flags(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = new SimpleData(name: $name, age: $age, email: null);
        $json = $data->toJson(JSON_PRETTY_PRINT);

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

        $data = new SimpleData(name: $name, age: $age, email: $email);
        $json = $data->toJson(0, $context);

        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertArrayHasKey('name', $decoded);
        $this->assertArrayHasKey('age', $decoded);
        $this->assertArrayNotHasKey('email', $decoded);
    }

    public function test_json_serialize(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = new SimpleData(name: $name, age: $age, email: null);
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        $this->assertIsString($json);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($name, $decoded['name']);
        $this->assertSame($age, $decoded['age']);
    }

    public function test_mutability(): void
    {
        $originalName = $this->faker->name();
        $originalAge = $this->faker->numberBetween(18, 99);
        $newName = $this->faker->name();
        $newAge = $this->faker->numberBetween(100, 150);

        $data = new SimpleData(name: $originalName, age: $originalAge, email: null);

        $data->name = $newName;
        $data->age = $newAge;

        $this->assertSame($newName, $data->name);
        $this->assertSame($newAge, $data->age);
    }

    public function test_mutable_properties_can_be_changed(): void
    {
        $data = new SimpleData(
            name: $this->faker->name(),
            age: $this->faker->numberBetween(18, 99),
            email: null,
        );

        $newEmail = $this->faker->email();
        $data->email = $newEmail;

        $this->assertSame($newEmail, $data->email);
    }
}
