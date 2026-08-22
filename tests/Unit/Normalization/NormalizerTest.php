<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDiscriminatorSelfDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureLazyDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureMixedHolderDto;
use JOOservices\Dto\Tests\Fixtures\NoConstructorDto;
use JOOservices\Dto\Tests\Fixtures\NonPublicPromotedDto;
use JOOservices\Dto\Tests\Fixtures\ProfileDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use JsonSerializable;
use ReflectionException;

final class NormalizerTest extends TestCase
{
    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testWrapAppliesConsistentlyToArrayAndToJson(): void
    {
        $dto = new UserDto('Jane', 30);
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withWrap('data'));

        $array = $dto->toArray($ctx);
        self::assertSame(['data' => ['name' => 'Jane', 'age' => 30, 'email' => null]], $array);

        $decoded = json_decode($dto->toJson($ctx), true, flags: JSON_THROW_ON_ERROR);
        self::assertSame($array, $decoded);
    }

    /**
     * Wrap is only a top-level envelope; nested objects must not be double-wrapped.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testWrapOnlyAppliesAtTopLevelNotNestedObjects(): void
    {
        $profile = new ProfileDto(new UserDto('Jane', 30), new AddressDto('Hanoi'));
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withWrap('payload'));

        $array = $profile->toArray($ctx);

        self::assertArrayHasKey('payload', $array);
        self::assertIsArray($array['payload']);
        self::assertSame(['name' => 'Jane', 'age' => 30, 'email' => null], $array['payload']['user']);
        self::assertArrayNotHasKey('payload', $array['payload']['user']);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testOnlyFiltersToRequestedProperties(): void
    {
        $dto = new UserDto('Jane', 30, 'jane@example.com');
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withOnly(['name']));

        self::assertSame(['name' => 'Jane'], $dto->toArray($ctx));
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testExceptExcludesRequestedProperties(): void
    {
        $dto = new UserDto('Jane', 30, 'jane@example.com');
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withExcept(['email']));

        self::assertSame(['name' => 'Jane', 'age' => 30], $dto->toArray($ctx));
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testMaxDepthZeroPreventsAnyNormalization(): void
    {
        $dto = new UserDto('Jane', 30);
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withMaxDepth(0));

        self::assertSame([], $dto->toArray($ctx));
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testMaxDepthOneStopsBeforeDescendingIntoNestedDtos(): void
    {
        $profile = new ProfileDto(new UserDto('Jane', 30), new AddressDto('Hanoi'));
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withMaxDepth(1));

        $array = $profile->toArray($ctx);

        self::assertSame([], $array['user']);
        self::assertSame([], $array['address']);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testLazyPropertiesAreExcludedByDefault(): void
    {
        $dto = new CoreFixtureLazyDto('sam');

        self::assertSame(['name' => 'sam'], $dto->toArray());
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testLazyPropertiesCanBeSelectivelyIncluded(): void
    {
        $dto = new CoreFixtureLazyDto('sam');
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withIncludeLazy(['upper']));

        $array = $dto->toArray($ctx);

        self::assertSame('SAM', $array['upper']);
        self::assertArrayNotHasKey('eager', $array);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testLazyPropertiesEmptyArrayIncludesAllOfThem(): void
    {
        $dto = new CoreFixtureLazyDto('sam');
        $ctx = (new Context(false, false))
            ->withSerializationOptions((new SerializationOptions())->withIncludeLazy([]));

        $array = $dto->toArray($ctx);

        self::assertSame('SAM', $array['upper']);
        self::assertSame('static-value', $array['eager']);
    }

    /**
     * The discriminator field is Hidden as a regular property, yet must still
     * surface in output because emitDiscriminator runs independently afterwards.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDiscriminatorIsEmittedEvenThoughItsOwnPropertyIsHidden(): void
    {
        $dto = CoreFixtureDiscriminatorSelfDto::fromArray(['kind' => 'self', 'label' => 'hi']);

        self::assertSame(['label' => 'hi', 'kind' => 'self'], $dto->toArray());
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testNestedObjectWithoutAConstructorPassesThroughUnchanged(): void
    {
        $inner = new NoConstructorDto();
        $holder = new CoreFixtureMixedHolderDto($inner);

        $array = $holder->toArray();

        self::assertSame($inner, $array['payload']);
    }

    /**
     * When meta creation for the nested value's class fails (e.g. an invalid
     * promoted-property visibility), normalize() falls back to the raw value
     * instead of blowing up the whole serialization.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testNestedObjectWhoseMetaCannotBeBuiltPassesThroughUnchanged(): void
    {
        $inner = new NonPublicPromotedDto('secret');
        $holder = new CoreFixtureMixedHolderDto($inner);

        $array = $holder->toArray();

        self::assertSame($inner, $array['payload']);
    }

    /**
     * C10: an INF scalar value must never be allowed to reach json_encode().
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testInfinityIsRejectedDuringNormalization(): void
    {
        $holder = new CoreFixtureMixedHolderDto(INF);

        try {
            $holder->toArray();
            self::fail('Expected HydrationException.');
        } catch (HydrationException $exception) {
            self::assertSame('INF and NaN cannot be serialized to JSON.', $exception->getMessage());
            self::assertSame('payload', $exception->getPath());
        }
    }

    /**
     * C10: NaN must be rejected the same way as INF.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testNanIsRejectedDuringNormalization(): void
    {
        $holder = new CoreFixtureMixedHolderDto(NAN);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('INF and NaN cannot be serialized to JSON.');

        $holder->toArray();
    }

    /**
     * Third-party JsonSerializable values that are not describable as DTOs must
     * serialize through jsonSerialize() so toArray() and toJson() stay aligned.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function testNestedJsonSerializableUsesJsonSerializeWhenMetaCannotDescribeIt(): void
    {
        $value = new class implements JsonSerializable {
            public function jsonSerialize(): mixed
            {
                return ['token' => 'abc'];
            }
        };
        $holder = new CoreFixtureMixedHolderDto($value);

        self::assertSame(['payload' => ['token' => 'abc']], $holder->toArray());
        self::assertSame(
            json_encode(['payload' => ['token' => 'abc']], JSON_THROW_ON_ERROR),
            $holder->toJson(),
        );
    }
}
