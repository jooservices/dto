<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Support;

use Faker\Generator;
use JOOservices\Dto\Tests\Fixtures\UserDto;

final class FakeUser
{
    /**
     * @return array{name: string, age: int, email: string}
     */
    public static function payload(Generator $faker): array
    {
        return [
            'name' => $faker->name(),
            'age' => $faker->numberBetween(18, 90),
            'email' => $faker->safeEmail(),
        ];
    }

    public static function dto(Generator $faker): UserDto
    {
        $payload = self::payload($faker);

        return new UserDto(
            name: $payload['name'],
            age: $payload['age'],
            email: $payload['email'],
        );
    }
}
