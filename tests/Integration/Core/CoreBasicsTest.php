<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Core;

use InvalidArgumentException;
use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Optional;
use JOOservices\Dto\Core\PackageInfo;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureMixedHolderDto;
use JOOservices\Dto\Tests\Fixtures\UserData;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\Support\FakeUser;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use ReflectionException;
use RuntimeException;

final class CoreBasicsTest extends TestCase
{
    public function testPackageInfoStillAvailable(): void
    {
        self::assertSame('jooservices/dto', PackageInfo::name());
    }

    public function testCastModeValues(): void
    {
        self::assertSame('strict', CastMode::Strict->value);
        self::assertSame('loose', CastMode::Loose->value);
        self::assertSame('permissive', CastMode::Permissive->value);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testContextStrictRejectsUnknownKeysByDefault(): void
    {
        $ctx = Context::strict();
        self::assertTrue($ctx->shouldRejectUnknownKeys());
        self::assertFalse($ctx->validationEnabled);
    }

    public function testSerializationOptionsOnly(): void
    {
        $options = (new SerializationOptions())->withOnly(['name']);
        self::assertTrue($options->shouldInclude('name'));
        self::assertFalse($options->shouldInclude('age'));
    }

    /**
     * @throws RuntimeException
     */
    public function testOptionalPresentAndEmpty(): void
    {
        $present = Optional::fromValue('x');
        self::assertTrue($present->isPresent());
        self::assertSame('x', $present->get());
        self::assertTrue(Optional::empty()->isEmpty());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoFromAndToArray(): void
    {
        $payload = FakeUser::payload($this->faker());
        $dto = UserDto::from($payload);
        self::assertSame($payload['name'], $dto->name);
        self::assertSame($payload['age'], $dto->age);
        self::assertSame($payload, $dto->toArray());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoWithNamedArgsPreservesState(): void
    {
        $payload = FakeUser::payload($this->faker());
        $newAge = $payload['age'] + $this->faker()->numberBetween(1, 10);
        $dto = new UserDto(name: $payload['name'], age: $payload['age']);
        $copy = $dto->with(age: $newAge);
        self::assertSame($payload['age'], $dto->age);
        self::assertSame($newAge, $copy->age);
        self::assertSame($payload['name'], $copy->name);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoWithArrayForm(): void
    {
        $payload = FakeUser::payload($this->faker());
        $newName = $this->faker()->name();
        $dto = UserDto::fromArray(['name' => $payload['name'], 'age' => $payload['age']]);
        $copy = $dto->with(['name' => $newName]);
        self::assertSame($newName, $copy->name);
        self::assertSame($payload['age'], $copy->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testC1HiddenPreservedOnWithUnknownPropertyFails(): void
    {
        $dto = FakeUser::dto($this->faker());
        $this->expectException(MappingException::class);
        $dto->with(['missing' => true]);
    }

    public function testTryFromReturnsNullOnFailure(): void
    {
        self::assertNull(UserDto::tryFrom(['name' => $this->faker()->name()]));
    }

    /**
     * @throws JsonException
     */
    public function testHashIsStableForSameState(): void
    {
        $payload = FakeUser::payload($this->faker());
        $a = new UserDto(name: $payload['name'], age: $payload['age']);
        $b = new UserDto(name: $payload['name'], age: $payload['age']);
        self::assertSame($a->hash(), $b->hash());
        self::assertTrue($a->equals($b));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDataSetMutatesInstance(): void
    {
        $payload = FakeUser::payload($this->faker());
        $data = UserData::from($payload);
        $newAge = $payload['age'] + 1;
        $data->set(['age' => $newAge]);
        self::assertSame($newAge, $data->age);
    }

    /**
     * Regression: a promoted constructor property literally typed `mixed`
     * must hydrate through fromArray()/from() — it must not be mistaken for
     * an unsupported "builtin" type with no matching caster.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMixedTypedPropertyHydratesThroughFromArray(): void
    {
        $dto = CoreFixtureMixedHolderDto::fromArray(['payload' => ['nested' => true]]);

        self::assertSame(['nested' => true], $dto->payload);
    }
}
