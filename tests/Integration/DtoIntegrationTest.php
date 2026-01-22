<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration;

use DateTimeImmutable;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class DtoIntegrationTest extends TestCase
{
    public function test_from_array_with_simple_dto(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $email = $this->faker->email();

        $dto = SimpleDto::fromArray([
            'name' => $name,
            'age' => $age,
            'email' => $email,
        ]);

        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
        $this->assertSame($email, $dto->email);
    }

    public function test_from_array_with_optional_property(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = SimpleDto::fromArray([
            'name' => $name,
            'age' => $age,
        ]);

        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
        $this->assertNull($dto->email);
    }

    public function test_from_json_with_simple_dto(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $json = json_encode([
            'name' => $name,
            'age' => $age,
        ], JSON_THROW_ON_ERROR);

        $dto = SimpleDto::fromJson($json);

        $this->assertSame($name, $dto->name);
        $this->assertSame($age, $dto->age);
    }

    public function test_from_array_with_nested_dto(): void
    {
        $userId = $this->faker->uuid();
        $email = $this->faker->email();
        $name = $this->faker->name();
        $dateString = '2026-01-15T10:30:00+00:00';

        $dto = UserDto::fromArray([
            'id' => $userId,
            'email_address' => $email,
            'name' => $name,
            'createdAt' => $dateString,
            'address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->country(),
            ],
            'status' => 'active',
        ]);

        $this->assertSame($userId, $dto->id);
        $this->assertSame($email, $dto->email);
        $this->assertSame($name, $dto->name);
        $this->assertInstanceOf(DateTimeImmutable::class, $dto->createdAt);
        $this->assertInstanceOf(AddressDto::class, $dto->address);
        $this->assertSame(Status::Active, $dto->status);
    }

    public function test_to_array(): void
    {
        $dto = SimpleDto::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
            'email' => $this->faker->email(),
        ]);

        $array = $dto->toArray();

        $this->assertSame($dto->name, $array['name']);
        $this->assertSame($dto->age, $array['age']);
        $this->assertSame($dto->email, $array['email']);
    }

    public function test_to_json(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = SimpleDto::fromArray([
            'name' => $name,
            'age' => $age,
        ]);

        $json = $dto->toJson();
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($name, $decoded['name']);
        $this->assertSame($age, $decoded['age']);
    }

    public function test_json_serialize(): void
    {
        $dto = SimpleDto::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ]);

        $json = json_encode($dto, JSON_THROW_ON_ERROR);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($dto->name, $decoded['name']);
        $this->assertSame($dto->age, $decoded['age']);
    }

    public function test_hidden_property_not_included_in_output(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'passwordHash' => 'secret_hash_value',
        ]);

        $array = $dto->toArray();

        $this->assertArrayNotHasKey('passwordHash', $array);
        $this->assertSame('secret_hash_value', $dto->passwordHash);
    }

    public function test_with_context_serialization_options(): void
    {
        $dto = SimpleDto::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
            'email' => $this->faker->email(),
        ]);

        $context = new Context(
            serializationOptions: new SerializationOptions(
                only: ['name', 'age'],
            ),
        );

        $array = $dto->toArray($context);

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayNotHasKey('email', $array);
    }

    public function test_with_context_except_option(): void
    {
        $dto = SimpleDto::fromArray([
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
            'email' => $this->faker->email(),
        ]);

        $context = new Context(
            serializationOptions: new SerializationOptions(
                except: ['email'],
            ),
        );

        $array = $dto->toArray($context);

        $this->assertArrayHasKey('name', $array);
        $this->assertArrayHasKey('age', $array);
        $this->assertArrayNotHasKey('email', $array);
    }

    public function test_nested_dto_normalization(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'address' => [
                'street' => $streetAddress = $this->faker->streetAddress(),
                'city' => $city = $this->faker->city(),
                'country' => $country = $this->faker->country(),
            ],
            'status' => 'active',
        ]);

        $array = $dto->toArray();

        $this->assertIsArray($array['address']);
        $this->assertSame($streetAddress, $array['address']['street']);
        $this->assertSame($city, $array['address']['city']);
        $this->assertSame($country, $array['address']['country']);
    }

    public function test_date_time_transformation(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
        ]);

        $array = $dto->toArray();

        $this->assertIsString($array['createdAt']);
        $this->assertStringContainsString('2026-01-15', $array['createdAt']);
    }

    public function test_enum_transformation(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'status' => 'inactive',
        ]);

        $array = $dto->toArray();

        $this->assertSame('inactive', $array['status']);
    }

    public function test_round_trip(): void
    {
        $originalData = [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
            'email' => $this->faker->email(),
        ];

        $dto1 = SimpleDto::fromArray($originalData);
        $array1 = $dto1->toArray();
        $dto2 = SimpleDto::fromArray($array1);

        $this->assertSame($dto1->name, $dto2->name);
        $this->assertSame($dto1->age, $dto2->age);
        $this->assertSame($dto1->email, $dto2->email);
    }

    public function test_from_object_with_std_class(): void
    {
        $obj = new stdClass;
        $obj->name = $this->faker->name();
        $obj->age = $this->faker->numberBetween(18, 99);

        $dto = SimpleDto::fromObject($obj);

        $this->assertSame($obj->name, $dto->name);
        $this->assertSame($obj->age, $dto->age);
    }

    public function test_from_with_auto_detection(): void
    {
        $data = [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $dtoFromArray = SimpleDto::from($data);
        $this->assertSame($data['name'], $dtoFromArray->name);

        $json = json_encode($data, JSON_THROW_ON_ERROR);
        $dtoFromJson = SimpleDto::from($json);
        $this->assertSame($data['name'], $dtoFromJson->name);
    }
}
