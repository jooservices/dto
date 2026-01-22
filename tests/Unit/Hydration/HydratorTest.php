<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Casting\Casters\DateTimeCaster;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\Hydrator;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;

final class HydratorTest extends TestCase
{
    private Hydrator $hydrator;

    private MetaFactory $metaFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = new MemoryMetaCache;
        $this->metaFactory = new MetaFactory($cache);
        $mapper = new Mapper;
        $casterRegistry = new CasterRegistry;
        $casterRegistry->register(new DateTimeCaster);

        $this->hydrator = new Hydrator($mapper, $casterRegistry, $this->metaFactory);
    }

    public function test_hydrate_simple_dto(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = [
            'name' => $name,
            'age' => $age,
        ];

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertInstanceOf(SimpleDto::class, $result);
        $this->assertSame($name, $result->name);
        $this->assertSame($age, $result->age);
    }

    public function test_hydrate_with_optional_property(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = [
            'name' => $name,
            'age' => $age,
        ];

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertInstanceOf(SimpleDto::class, $result);
        $this->assertNull($result->email);
    }

    public function test_hydrate_with_default_value(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = [
            'name' => $name,
            'age' => $age,
        ];

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertNull($result->email);
    }

    public function test_hydrate_throws_exception_for_missing_required_property(): void
    {
        $data = [
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $meta = $this->metaFactory->create(SimpleDto::class);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Failed to hydrate');

        $this->hydrator->hydrate($meta, $data, null);
    }

    public function test_hydrate_with_nested_dto(): void
    {
        $data = [
            'id' => $this->faker->uuid(),
            'email_address' => $this->faker->email(),
            'name' => $this->faker->name(),
            'createdAt' => '2026-01-15T10:30:00+00:00',
            'address' => [
                'street' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'country' => $this->faker->country(),
            ],
        ];

        $meta = $this->metaFactory->create(UserDto::class);
        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertInstanceOf(UserDto::class, $result);
        $this->assertInstanceOf(AddressDto::class, $result->address);
    }

    public function test_hydrate_with_null_value(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);

        $data = [
            'name' => $name,
            'age' => $age,
            'email' => null,
        ];

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertNull($result->email);
    }

    public function test_hydrate_aggregates_multiple_errors(): void
    {
        $testClass = new class('test', 42)
        {
            public function __construct(
                public readonly string $required1,
                public readonly int $required2,
            ) {}
        };

        $meta = $this->metaFactory->create($testClass::class);

        try {
            $this->hydrator->hydrate($meta, [], null);
            $this->fail('Expected HydrationException to be thrown');
        } catch (HydrationException $e) {
            $this->assertStringContainsString('Failed to hydrate', $e->getMessage());
        }
    }

    public function test_hydrate_builds_constructor_args_in_correct_order(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $email = $this->faker->email();

        $data = [
            'name' => $name,
            'age' => $age,
            'email' => $email,
        ];

        $meta = $this->metaFactory->create(SimpleDto::class);
        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertSame($name, $result->name);
        $this->assertSame($age, $result->age);
        $this->assertSame($email, $result->email);
    }

    public function test_hydrate_skips_properties_not_in_constructor(): void
    {
        $testClass = new class('test')
        {
            public string $notInConstructor = 'default';

            public function __construct(
                public readonly string $inConstructor,
            ) {}
        };

        $meta = $this->metaFactory->create($testClass::class);
        $result = $this->hydrator->hydrate($meta, ['inConstructor' => 'value'], null);

        $this->assertSame('value', $result->inConstructor);
    }

    public function test_hydrate_with_typed_array(): void
    {
        $testClass = new class([])
        {
            /**
             * @param  array<AddressDto>  $addresses
             */
            public function __construct(
                public readonly array $addresses,
            ) {}
        };

        $data = [
            'addresses' => [
                [
                    'street' => $this->faker->streetAddress(),
                    'city' => $this->faker->city(),
                    'country' => $this->faker->country(),
                ],
            ],
        ];

        $meta = new ClassMeta(
            className: $testClass::class,
            isReadonly: true,
            properties: [
                'addresses' => new PropertyMeta(
                    name: 'addresses',
                    type: new TypeDescriptor(
                        name: 'array',
                        isBuiltin: true,
                        isNullable: false,
                        isArray: true,
                        arrayItemType: new TypeDescriptor(
                            name: AddressDto::class,
                            isBuiltin: false,
                            isNullable: false,
                            isArray: false,
                            arrayItemType: null,
                            isEnum: false,
                            enumClass: null,
                            isDto: true,
                            isDateTime: false,
                        ),
                        isEnum: false,
                        enumClass: null,
                        isDto: false,
                        isDateTime: false,
                    ),
                    isReadonly: true,
                    hasDefault: false,
                    defaultValue: null,
                    mapFrom: null,
                    casterClass: null,
                    transformerClass: null,
                    isHidden: false,
                    validationRules: [],
                    attributes: [],
                ),
            ],
            constructorParams: ['addresses'],
            attributes: [],
        );

        $result = $this->hydrator->hydrate($meta, $data, null);

        $this->assertIsArray($result->addresses);
        $this->assertNotEmpty($result->addresses);
        $this->assertInstanceOf(AddressDto::class, $result->addresses[0]);
    }
}
