<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Normalizer;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class NormalizerTest extends TestCase
{
    private Normalizer $normalizer;

    private MetaFactory $metaFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = new MemoryMetaCache;
        $this->metaFactory = new MetaFactory($cache);

        $transformerRegistry = new TransformerRegistry;
        $transformerRegistry->register(new DateTimeTransformer, 10);
        $transformerRegistry->register(new EnumTransformer, 20);

        $this->normalizer = new Normalizer($transformerRegistry, $this->metaFactory);
    }

    public function test_normalize_simple_dto(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $dto = new SimpleDto(
            name: $name,
            age: $age,
            email: null,
        );

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        $this->assertSame($name, $result['name']);
        $this->assertSame($age, $result['age']);
    }

    public function test_normalize_excludes_hidden_properties(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'passwordHash' => 'secret',
        ]);

        $meta = $this->metaFactory->create(UserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        $this->assertArrayNotHasKey('passwordHash', $result);
    }

    public function test_normalize_with_serialization_options(): void
    {
        $dto = new SimpleDto(
            name: $this->faker->name(),
            age: $this->faker->numberBetween(18, 99),
            email: $this->faker->email(),
        );

        $context = new Context(
            serializationOptions: new SerializationOptions(
                only: ['name', 'age'],
            ),
        );

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayNotHasKey('email', $result);
    }

    public function test_normalize_with_except_option(): void
    {
        $dto = new SimpleDto(
            name: $this->faker->name(),
            age: $this->faker->numberBetween(18, 99),
            email: $this->faker->email(),
        );

        $context = new Context(
            serializationOptions: new SerializationOptions(
                except: ['email'],
            ),
        );

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('age', $result);
        $this->assertArrayNotHasKey('email', $result);
    }

    public function test_normalize_nested_dto(): void
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
        ]);

        $meta = $this->metaFactory->create(UserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        $this->assertIsArray($result['address']);
        $this->assertSame($streetAddress, $result['address']['street']);
        $this->assertSame($city, $result['address']['city']);
        $this->assertSame($country, $result['address']['country']);
    }

    public function test_normalize_with_depth_limit(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->country(),
            ],
        ]);

        $context = new Context(
            serializationOptions: new SerializationOptions(
                maxDepth: 0,
            ),
        );

        $meta = $this->metaFactory->create(UserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertEmpty($result);
    }

    public function test_normalize_transforms_date_time(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
        ]);

        $meta = $this->metaFactory->create(UserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        $this->assertIsString($result['createdAt']);
        $this->assertStringContainsString('2026-01-15', $result['createdAt']);
    }

    public function test_normalize_transforms_enum(): void
    {
        $dto = UserDto::fromArray([
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'status' => 'active',
        ]);

        $meta = $this->metaFactory->create(UserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        $this->assertSame('active', $result['status']);
    }

    public function test_normalize_handles_null_values(): void
    {
        $dto = new SimpleDto(
            name: $this->faker->name(),
            age: $this->faker->numberBetween(18, 99),
            email: null,
        );

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        $this->assertNull($result['email']);
    }

    public function test_normalize_array(): void
    {
        $testClass = new readonly class([new AddressDto('Street', 'City', 'Country')])
        {
            /**
             * @param  array<AddressDto>  $addresses
             */
            public function __construct(
                public array $addresses,
            ) {}
        };

        $dto = new $testClass([
            new AddressDto(
                street: $street = $this->faker->streetAddress(),
                city: $city = $this->faker->city(),
                country: $country = $this->faker->country(),
            ),
        ]);

        $cache = new MemoryMetaCache;
        $metaFactory = new MetaFactory($cache);
        $transformerRegistry = new TransformerRegistry;
        $normalizer = new Normalizer($transformerRegistry, $metaFactory);

        $meta = $metaFactory->create($testClass::class);
        $result = $normalizer->normalize($dto, $meta, null);

        $this->assertIsArray($result['addresses']);
        $this->assertNotEmpty($result['addresses']);
        $this->assertSame($street, $result['addresses'][0]['street']);
        $this->assertSame($city, $result['addresses'][0]['city']);
        $this->assertSame($country, $result['addresses'][0]['country']);
    }

    public function test_normalize_generic_object(): void
    {
        $obj = new stdClass;
        $obj->property = 'value';

        $testClass = new readonly class(new stdClass)
        {
            public function __construct(
                public stdClass $data,
            ) {}
        };

        $dto = new $testClass($obj);

        $cache = new MemoryMetaCache;
        $metaFactory = new MetaFactory($cache);
        $transformerRegistry = new TransformerRegistry;
        $normalizer = new Normalizer($transformerRegistry, $metaFactory);

        $meta = $metaFactory->create($testClass::class);
        $result = $normalizer->normalize($dto, $meta, null);

        $this->assertIsArray($result['data']);
        $this->assertSame('value', $result['data']['property']);
    }

    public function test_normalize_with_max_depth_in_nested_array(): void
    {
        $testClass = new readonly class([new AddressDto('S', 'C', 'Co')])
        {
            /**
             * @param  array<AddressDto>  $addresses
             */
            public function __construct(
                public array $addresses,
            ) {}
        };

        $dto = new $testClass([
            new AddressDto('Street', 'City', 'Country'),
        ]);

        $context = new Context(
            serializationOptions: new SerializationOptions(
                maxDepth: 1,
            ),
        );

        $cache = new MemoryMetaCache;
        $metaFactory = new MetaFactory($cache);
        $transformerRegistry = new TransformerRegistry;
        $normalizer = new Normalizer($transformerRegistry, $metaFactory);

        $meta = $metaFactory->create($testClass::class);
        $result = $normalizer->normalize($dto, $meta, $context, 0);

        $this->assertIsArray($result['addresses']);
    }
}
