<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Engine\EngineFactory;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionTypedDto;
use JOOservices\Dto\Tests\Fixtures\ProfileDto;
use JOOservices\Dto\Tests\Fixtures\UnionHolderDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\Support\FakeUser;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use ReflectionException;

final class NestedHydrationIntegrationTest extends TestCase
{
    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testEngineNormalizesNestedProfileGraph(): void
    {
        $user = FakeUser::dto($this->faker());
        $profile = new ProfileDto($user, new AddressDto($this->faker()->city()));
        $engine = EngineFactory::createDefault();

        $normalized = $engine->normalize($profile);

        self::assertIsArray($normalized['user']);
        self::assertIsArray($normalized['address']);
        self::assertSame($user->name, $normalized['user']['name']);
        self::assertIsString($engine->normalizeToJson($user));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnionAndIntersectionHoldersRoundTrip(): void
    {
        $union = UnionHolderDto::fromArray(['value' => 42]);
        $subject = IntersectionHolderDto::fromArray(['name' => 'tag']);
        $intersection = IntersectionTypedDto::fromArray(['subject' => $subject]);

        self::assertSame(42, $union->value);
        self::assertInstanceOf(IntersectionHolderDto::class, $intersection->subject);
        self::assertSame('tag', $intersection->subject->name);
        self::assertSame(3, $intersection->subject->count());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUserDtoRoundTripThroughJson(): void
    {
        $payload = FakeUser::payload($this->faker());
        $dto = UserDto::fromJson(json_encode($payload, JSON_THROW_ON_ERROR));

        self::assertSame($payload['name'], $dto->name);
        self::assertSame($payload['age'], $dto->age);
        self::assertSame(json_encode($payload, JSON_THROW_ON_ERROR), $dto->toJson());
    }
}
