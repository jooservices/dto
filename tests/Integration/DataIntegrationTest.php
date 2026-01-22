<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Tests\Fixtures\SimpleData;
use JOOservices\Dto\Tests\TestCase;

final class DataIntegrationTest extends TestCase
{
    public function test_from_array_with_simple_data(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = SimpleData::fromArray([
            'name' => $name,
            'age' => $age,
        ]);

        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }

    public function test_update_with_patch(): void
    {
        $data = SimpleData::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ]);

        $newName = $this->faker->name();
        $newAge = $this->faker->numberBetween(18, 99);

        $data->update([
            'name' => $newName,
            'age' => $newAge,
        ]);

        $this->assertSame($newName, $data->name);
        $this->assertSame($newAge, $data->age);
    }

    public function test_update_with_partial_patch(): void
    {
        $originalName = $this->faker->name();
        $originalAge = $this->faker->numberBetween(18, 99);

        $data = SimpleData::fromArray([
            'name' => $originalName,
            'age' => $originalAge,
        ]);

        $newName = $this->faker->name();
        $data->update(['name' => $newName]);

        $this->assertSame($newName, $data->name);
        $this->assertSame($originalAge, $data->age);
    }

    public function test_set_single_property(): void
    {
        $data = SimpleData::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ]);

        $newEmail = $this->faker->email();
        $data->set('email', $newEmail);

        $this->assertSame($newEmail, $data->email);
    }

    public function test_update_throws_for_non_existent_property(): void
    {
        $data = SimpleData::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ]);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage("Property 'nonExistent' does not exist");

        $data->update(['nonExistent' => 'value']);
    }

    public function test_data_extends_dto(): void
    {
        $data = SimpleData::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ]);

        $array = $data->toArray();

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
    }

    public function test_data_can_be_json_serialized(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = SimpleData::fromArray([
            'name' => $name,
            'age' => $age,
        ]);

        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($name, $decoded['name']);
        $this->assertSame($age, $decoded['age']);
    }

    public function test_mutation_preserves_other_properties(): void
    {
        $originalName = $this->faker->name();
        $originalAge = $this->faker->numberBetween(18, 99);
        $originalEmail = $this->faker->email();

        $data = SimpleData::fromArray([
            'name' => $originalName,
            'age' => $originalAge,
            'email' => $originalEmail,
        ]);

        $data->set('name', $this->faker->name());

        $this->assertSame($originalAge, $data->age);
        $this->assertSame($originalEmail, $data->email);
    }

    public function test_multiple_updates_in_sequence(): void
    {
        $data = SimpleData::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ]);

        $name1 = $this->faker->name();
        $name2 = $this->faker->name();
        $name3 = $this->faker->name();

        $data->set('name', $name1);
        $this->assertSame($name1, $data->name);

        $data->set('name', $name2);
        $this->assertSame($name2, $data->name);

        $data->set('name', $name3);
        $this->assertSame($name3, $data->name);
    }

    public function test_update_with_empty_patch(): void
    {
        $originalName = $this->faker->name();
        $originalAge = $this->faker->numberBetween(18, 99);

        $data = SimpleData::fromArray([
            'name' => $originalName,
            'age' => $originalAge,
        ]);

        $data->update([]);

        $this->assertSame($originalName, $data->name);
        $this->assertSame($originalAge, $data->age);
    }

    public function test_set_null_value(): void
    {
        $data = SimpleData::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
            'email' => $this->faker->email(),
        ]);

        $data->set('email', null);

        $this->assertNull($data->email);
    }

    public function test_from_json_with_data(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $json = json_encode([
            'name' => $name,
            'age' => $age,
        ], JSON_THROW_ON_ERROR);

        $data = SimpleData::fromJson($json);

        $this->assertSame($name, $data->name);
        $this->assertSame($age, $data->age);
    }
}
