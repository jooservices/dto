<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Support;

use Faker\Generator;
use InvalidArgumentException;
use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Optional;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Engine\EngineFactory;
use JOOservices\Dto\Engine\EngineHolder;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\ExceptionPayloadRedactorInterface;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\FileMetaCache;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\EnumHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionTypedDto;
use JOOservices\Dto\Tests\Fixtures\NoConstructorDto;
use JOOservices\Dto\Tests\Fixtures\NonPromotedDto;
use JOOservices\Dto\Tests\Fixtures\NonPublicPromotedDto;
use JOOservices\Dto\Tests\Fixtures\ProfileDto;
use JOOservices\Dto\Tests\Fixtures\UnionHolderDto;
use JOOservices\Dto\Tests\Fixtures\UserData;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JsonException;
use PHPUnit\Framework\Assert;
use ReflectionException;
use RuntimeException;
use stdClass;

/**
 * Shared end-to-end scenarios for {@see LibraryRegressionTest} in the Integration suite.
 */
final class CoverageExercises
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ValidationException
     * @throws ReflectionException
     */
    public static function runAbstractDtoAndDataScenarios(Generator $faker): void
    {
        $payload = FakeUser::payload($faker);
        $dto = UserDto::fromJson(json_encode($payload, JSON_THROW_ON_ERROR));
        Assert::assertSame($payload['email'], $dto->email);
        Assert::assertSame($dto->name, UserDto::fromObject($dto)->name);

        $clone = $dto->clone();
        Assert::assertTrue($clone->equals($dto));
        Assert::assertSame($clone->name, $dto->replicate()->name);

        $newAge = $faker->numberBetween(91, 120);
        $merged = $dto->merge(['age' => $newAge]);
        Assert::assertSame($newAge, $merged->age);

        $newEmail = $faker->safeEmail();
        $recursive = $dto->mergeRecursive(['email' => $newEmail]);
        Assert::assertSame($newEmail, $recursive->email);

        $otherAge = $newAge + 1;
        $other = $dto->with(age: $otherAge);
        $diff = $dto->diff($other);
        Assert::assertArrayHasKey('age', $diff);
        Assert::assertSame($payload['age'], $diff['age']['old']);
        Assert::assertSame($otherAge, $diff['age']['new']);

        $otherName = $faker->name();
        $extraDiff = $other->diff(new UserDto(name: $otherName, age: $payload['age']));
        Assert::assertArrayHasKey('name', $extraDiff);

        Assert::assertSame(
            json_encode($dto->toArray(), JSON_THROW_ON_ERROR),
            $dto->toJson(),
        );
        Assert::assertSame($dto->toArray(), $dto->jsonSerialize());

        $whenResult = $dto->when(true, static fn(UserDto $value): string => $value->name);
        Assert::assertSame($payload['name'], $whenResult);
        Assert::assertSame($dto, $dto->when(false, static fn(UserDto $value): string => $value->name));

        $unlessLabel = $faker->word();
        Assert::assertSame(
            $unlessLabel,
            $dto->unless(false, static fn(UserDto $value): string => $unlessLabel),
        );

        $dto->validate();
        $dto->with();

        $updatedName = $faker->name();
        $data = new UserData(name: $payload['name'], age: $payload['age']);
        $data->update(['name' => $updatedName]);
        Assert::assertSame($updatedName, $data->name);

        Assert::assertNull(UserDto::tryFrom($faker->randomNumber()));
        Assert::assertInstanceOf(
            UserDto::class,
            UserDto::tryFrom(['name' => $payload['name'], 'age' => $payload['age']]),
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function runContextAndSerializationOptionsScenarios(Generator $faker): void
    {
        $permissive = Context::permissive();
        Assert::assertSame(CastMode::Permissive->value, $permissive->castMode);
        Assert::assertFalse($permissive->shouldRejectUnknownKeys());
        Assert::assertFalse($permissive->shouldDisallowScalarCoercion());

        $traceKey = $faker->word();
        $wrap = $faker->word();
        $ctx = Context::strict()
            ->withNamingStrategy(null)
            ->withValidationEnabled(true)
            ->withSerializationOptions(new SerializationOptions(wrap: $wrap))
            ->withCustomData([$traceKey => true])
            ->withCastMode(CastMode::Loose)
            ->withGlobalPipeline([])
            ->withRejectUnknownKeys(false)
            ->withDisallowScalarCoercion(true)
            ->withSourceNamingOnOutput(true);

        Assert::assertTrue($ctx->validationEnabled);
        Assert::assertSame([$traceKey => true], $ctx->customData);
        Assert::assertFalse($ctx->shouldRejectUnknownKeys());
        Assert::assertTrue($ctx->shouldDisallowScalarCoercion());
        Assert::assertSame($wrap, $ctx->serializationOptionsOrDefault()->wrap);

        $options = (new SerializationOptions())
            ->withOnly(['name'])
            ->withExcept(['secret'])
            ->withMaxDepth(3)
            ->withIncludeLazy([])
            ->withWrap($wrap);

        Assert::assertTrue($options->shouldInclude('anything'));
        Assert::assertTrue($options->shouldIncludeLazy('lazy'));
        Assert::assertTrue($options->canDescend(0));
        Assert::assertFalse($options->canDescend(3));
    }

    /**
     * @throws RuntimeException
     */
    public static function runOptionalScenarios(Generator $faker): void
    {
        $value = $faker->word();
        $fallback = $faker->word();
        $generated = $faker->word();

        $optional = Optional::fromValue($value);
        Assert::assertSame($value, $optional->get());
        Assert::assertSame($value, $optional->orElse($fallback));
        Assert::assertSame($value, $optional->orElseGet(static fn(): string => $fallback));
        Assert::assertSame(
            $value,
            $optional->orElseThrow(static fn(): RuntimeException => new RuntimeException($fallback)),
        );

        $seen = null;
        $optional->ifPresent(static function (string $present) use (&$seen): void {
            $seen = $present;
        });
        Assert::assertSame($value, $seen);

        $mapped = $optional->map(static fn(string $present): int => strlen($present));
        Assert::assertTrue($mapped->isPresent());
        Assert::assertSame(strlen($value), $mapped->get());

        $filtered = $optional->filter(static fn(string $present): bool => $present !== '');
        Assert::assertTrue($filtered->isPresent());

        $empty = Optional::empty();
        Assert::assertTrue($empty->isEmpty());
        Assert::assertSame($fallback, $empty->orElse($fallback));
        Assert::assertSame($generated, $empty->orElseGet(static fn(): string => $generated));

        $empty->ifEmpty(static function (): void {
        });

        try {
            $empty->get();
            Assert::fail('Expected RuntimeException');
        } catch (RuntimeException) {
        }

        try {
            $empty->orElseThrow(static fn(): RuntimeException => new RuntimeException($fallback));
            Assert::fail('Expected RuntimeException');
        } catch (RuntimeException) {
        }

        Assert::assertTrue($empty->map(static fn(mixed $present): mixed => $present)->isEmpty());
        Assert::assertTrue($optional->filter(static fn(string $present): bool => $present === $fallback)->isEmpty());
    }

    public static function runExceptionScenarios(Generator $faker): void
    {
        $path = $faker->word();
        $given = $faker->word();
        $mapping = new MappingException(
            message: $faker->sentence(),
            path: $path,
            expectedType: UserDto::class,
            givenType: 'string',
            givenValue: $given,
        );

        $nestedPath = $faker->word() . '.' . $path;
        $withPath = $mapping->withPath($nestedPath);
        Assert::assertSame($nestedPath, $withPath->getPath());

        $root = $faker->word();
        Assert::assertSame($root . '.' . $path, $mapping->prependPath($root)->getPath());

        $redacted = $mapping->withPayloadRedactor(new readonly class implements ExceptionPayloadRedactorInterface {
            public function redact(string $path, mixed $value): mixed
            {
                return '[redacted:' . $path . ']';
            }
        });

        $payload = $redacted->toArray();
        Assert::assertSame('[redacted:' . $path . ']', $payload['givenValue']);
        Assert::assertStringContainsString($mapping->getMessage(), $mapping->getFullMessage());
        Assert::assertStringContainsString('path: ' . $path, $mapping->getFullMessage());

        $hydration = new HydrationException(
            message: $faker->sentence(),
            errors: [$mapping],
        );
        Assert::assertCount(1, $hydration->getErrors());
        Assert::assertArrayHasKey('errors', $hydration->toArray());

        $validation = new ValidationException(
            message: $faker->sentence(),
            path: 'name',
            violations: [new RuleViolation('name', 'required', $faker->sentence(), '')],
        );
        Assert::assertCount(1, $validation->getViolations());
        Assert::assertArrayHasKey('violations', $validation->toArray());

        $clearedPath = $mapping->withPath('');
        Assert::assertSame('', $clearedPath->getPath());

        $cast = new CastException(message: $faker->sentence(), path: 'age');
        Assert::assertSame('age', $cast->getPath());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException

     * @throws ValidationException*/
    public static function runEngineScenarios(Generator $faker): void
    {
        $engine = EngineFactory::createDefault();

        try {
            $engine->normalizeInput('{invalid');
            Assert::fail('Expected HydrationException');
        } catch (HydrationException) {
        }

        try {
            $engine->normalizeInput('"' . $faker->word() . '"');
            Assert::fail('Expected HydrationException');
        } catch (HydrationException) {
        }

        try {
            $engine->normalizeInput($faker->randomNumber());
            Assert::fail('Expected HydrationException');
        } catch (HydrationException) {
        }

        $object = new stdClass();
        $name = $faker->name();
        $object->name = $name;
        Assert::assertSame(['name' => $name], $engine->normalizeInput($object));

        $payload = FakeUser::payload($faker);

        try {
            $engine->hydrate(
                UserDto::class,
                [...$payload, '__unknown_' . $faker->word() => true],
                Context::strict(),
            );
            Assert::fail('Expected MappingException');
        } catch (MappingException) {
        }

        $user = FakeUser::dto($faker);
        $city = $faker->city();
        $address = new AddressDto(city: $city);
        $profile = new ProfileDto(user: $user, address: $address);
        $normalized = $engine->normalize($profile);
        Assert::assertIsArray($normalized['user']);
        Assert::assertIsArray($normalized['address']);
        Assert::assertSame($user->name, $normalized['user']['name']);
        Assert::assertSame($city, $normalized['address']['city']);
        Assert::assertIsString($engine->normalizeToJson($user));

        $engine->validate($user, new Context(validationEnabled: true, sourceKeyOut: false));
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function runEngineHolderAndFactoryScenarios(Generator $faker): void
    {
        unset($faker);
        $custom = (new EngineFactory())
            ->withMetaCache(new MemoryMetaCache())
            ->create();

        EngineHolder::set($custom);
        Assert::assertSame($custom, EngineHolder::get());
        EngineHolder::reset();
        Assert::assertNotSame($custom, EngineHolder::get());
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public static function runMetaFactoryScenarios(Generator $faker): void
    {
        unset($faker);
        $factory = new MetaFactory(new MemoryMetaCache());

        $userMeta = $factory->create(UserDto::class);
        Assert::assertNotNull($userMeta->property('name'));
        Assert::assertNull($userMeta->property('missing'));

        $factory->create(EnumHolderDto::class);
        $factory->create(UnionHolderDto::class);
        $factory->create(IntersectionTypedDto::class);
        $factory->create(IntersectionHolderDto::class);

        try {
            $factory->create('Missing\\Class');
            Assert::fail('Expected HydrationException');
        } catch (HydrationException) {
        }

        try {
            $factory->create(NonPromotedDto::class);
            Assert::fail('Expected HydrationException');
        } catch (HydrationException) {
        }

        try {
            $factory->create(NonPublicPromotedDto::class);
            Assert::fail('Expected HydrationException');
        } catch (HydrationException) {
        }

        $enumMeta = $factory->create(EnumHolderDto::class);
        Assert::assertSame('enum', $enumMeta->property('status')?->type->kind);
    }

    public static function runMemoryMetaCacheScenarios(Generator $faker): void
    {
        unset($faker);
        $cache = new MemoryMetaCache();
        $meta = new ClassMeta(
            className: UserDto::class,
            properties: [
                new PropertyMeta(
                    name: 'name',
                    type: TypeDescriptor::builtin('string'),
                    allowsNull: false,
                    hasDefault: false,
                ),
            ],
            ctorParams: ['name'],
            hasConstructor: true,
        );

        $cache->set(UserDto::class, $meta);
        Assert::assertTrue($cache->has(UserDto::class));
        Assert::assertSame($meta, $cache->get(UserDto::class));
        Assert::assertSame(1, $cache->getCount());
        Assert::assertSame([UserDto::class], $cache->getCachedClasses());

        $cache->clear(UserDto::class);
        Assert::assertFalse($cache->has(UserDto::class));

        $cache->set(UserDto::class, $meta);
        $cache->clear();
        Assert::assertSame(0, $cache->getCount());
    }

    /**
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    public static function runFileMetaCacheScenarios(Generator $faker): void
    {
        $directory = sys_get_temp_dir() . '/dto-meta-cache-' . $faker->lexify('????????');
        mkdir($directory);

        try {
            $signingKey = 'coverage-cache-signing-key';
            $cache = new FileMetaCache($directory, $signingKey, 2);
            $meta = new ClassMeta(
                className: UserDto::class,
                properties: [
                    new PropertyMeta(
                        name: 'name',
                        type: TypeDescriptor::mixed(),
                        allowsNull: false,
                        hasDefault: false,
                    ),
                ],
                ctorParams: ['name'],
                hasConstructor: true,
            );

            $cache->set(UserDto::class, $meta);
            Assert::assertTrue($cache->has(UserDto::class));
            Assert::assertSame($meta->className, $cache->get(UserDto::class)?->className);

            $cache->clear(UserDto::class);
            Assert::assertFalse($cache->has(UserDto::class));

            $cache->set(UserDto::class, $meta);
            $cache->clear();
            Assert::assertFalse($cache->has(UserDto::class));

            $cachePath = $directory . '/' . hash('xxh3', $signingKey . "\0" . UserDto::class) . '.cache';
            file_put_contents($cachePath, $faker->sentence());
            Assert::assertNull($cache->get(UserDto::class));
            @unlink($cachePath);
        } finally {
            $files = glob($directory . '/*');
            if ($files !== false) {
                array_map('unlink', $files);
            }
            rmdir($directory);
        }

        try {
            new FileMetaCache('/path/that/does/not/exist/' . $faker->slug(), 'key');
            Assert::fail('Expected InvalidArgumentException');
        } catch (InvalidArgumentException) {
        }
    }

    public static function runNoConstructorHydrationScenario(Generator $faker): void
    {
        unset($faker);
        $thrown = false;

        try {
            EngineFactory::createDefault()->hydrate(NoConstructorDto::class, []);
        } catch (
            HydrationException
            | MappingException
            | InvalidArgumentException
            | ReflectionException
            | CastException
            | ValidationException
        ) {
            $thrown = true;
        }

        Assert::assertTrue($thrown);
    }

    public static function runTypeDescriptorScenarios(Generator $faker): void
    {
        unset($faker);
        Assert::assertSame('mixed', TypeDescriptor::mixed()->kind);
        Assert::assertSame('builtin', TypeDescriptor::builtin('int')->kind);
        Assert::assertSame(UserDto::class, TypeDescriptor::classType(UserDto::class)->className);
    }
}
