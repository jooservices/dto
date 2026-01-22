<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Normalizer;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\LazyDtoWithNested;
use JOOservices\Dto\Tests\Fixtures\LazyUserDto;
use JOOservices\Dto\Tests\TestCase;
use LogicException;

final class LazyPropertiesTest extends TestCase
{
    private Normalizer $normalizer;

    private MetaFactory $metaFactory;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = new MemoryMetaCache;
        $this->metaFactory = new MetaFactory($cache);
        $transformerRegistry = new TransformerRegistry;
        $this->normalizer = new Normalizer($transformerRegistry, $this->metaFactory);
    }

    public function test_normalize_without_lazy_properties(): void
    {
        $dto = new LazyUserDto(
            firstName: $firstName = $this->faker->firstName(),
            lastName: $lastName = $this->faker->lastName(),
            email: $email = $this->faker->email(),
            age: $age = $this->faker->numberBetween(18, 99),
        );

        $meta = $this->metaFactory->create(LazyUserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, null);

        // Regular properties included
        $this->assertSame($firstName, $result['firstName']);
        $this->assertSame($lastName, $result['lastName']);
        $this->assertSame($email, $result['email']);
        $this->assertSame($age, $result['age']);

        // Lazy properties not included by default
        $this->assertArrayNotHasKey('fullName', $result);
        $this->assertArrayNotHasKey('initials', $result);
        $this->assertArrayNotHasKey('stats', $result);
    }

    public function test_normalize_with_single_lazy_property(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $options = new SerializationOptions(includeLazy: ['fullName']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyUserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('fullName', $result);
        $this->assertSame('John Doe', $result['fullName']);
        $this->assertArrayNotHasKey('initials', $result);
    }

    public function test_normalize_with_closure_lazy_property(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $options = new SerializationOptions(includeLazy: ['initials']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyUserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('initials', $result);
        $this->assertSame('JD', $result['initials']);
    }

    public function test_normalize_with_all_lazy_properties(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 25,
        );

        $options = new SerializationOptions(includeLazy: []);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyUserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('fullName', $result);
        $this->assertArrayHasKey('initials', $result);
        $this->assertArrayHasKey('stats', $result);
        $this->assertArrayHasKey('displayEmail', $result);

        $this->assertSame('John Doe', $result['fullName']);
        $this->assertSame('JD', $result['initials']);
        $this->assertIsArray($result['stats']);
    }

    public function test_closure_only_computed_when_included(): void
    {
        $computed = false;

        $testDto = new class('test') extends Dto implements ComputesLazyProperties
        {
            private bool $computedFlag = false;

            public function __construct(
                public string $name,
            ) {}

            public function computeLazyProperties(): array
            {
                return [
                    'expensive' => function () {
                        $this->computedFlag = true;

                        return 'value';
                    },
                ];
            }

            public function wasComputed(): bool
            {
                return $this->computedFlag;
            }
        };

        $dto = new $testDto('test');

        // Without includeLazy - closure not called
        $meta = $this->metaFactory->create($testDto::class);
        $this->normalizer->normalize($dto, $meta, null);
        $this->assertFalse($dto->wasComputed());

        // With includeLazy - closure called
        $options = new SerializationOptions(includeLazy: ['expensive']);
        $context = new Context(serializationOptions: $options);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertTrue($dto->wasComputed());
        $this->assertSame('value', $result['expensive']);
    }

    public function test_lazy_property_collision_throws_exception(): void
    {
        $testDto = new class('John') extends Dto implements ComputesLazyProperties
        {
            public function __construct(
                public string $firstName,
            ) {}

            public function computeLazyProperties(): array
            {
                return [
                    'firstName' => 'collision', // Conflicts with property
                ];
            }
        };

        $dto = new $testDto('John');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage("Lazy property 'firstName' conflicts with existing property");

        $options = new SerializationOptions(includeLazy: ['firstName']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create($testDto::class);
        $this->normalizer->normalize($dto, $meta, $context);
    }

    public function test_lazy_property_with_nested_dto(): void
    {
        $dto = new LazyDtoWithNested(
            name: 'Test',
            address: new AddressDto(
                street: $street = $this->faker->streetAddress(),
                city: $city = $this->faker->city(),
                country: $country = $this->faker->country(),
            ),
        );

        $options = new SerializationOptions(includeLazy: ['addressSummary']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyDtoWithNested::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('addressSummary', $result);
        $this->assertIsArray($result['addressSummary']);
        $this->assertSame($street, $result['addressSummary']['street']);
        $this->assertSame($city, $result['addressSummary']['city']);
        $this->assertSame($country, $result['addressSummary']['country']);
    }

    public function test_lazy_property_with_deep_nested_array(): void
    {
        $dto = new LazyDtoWithNested(
            name: $name = $this->faker->name(),
            address: new AddressDto(
                street: $street = $this->faker->streetAddress(),
                city: $city = $this->faker->city(),
                country: $country = $this->faker->country(),
            ),
        );

        $options = new SerializationOptions(includeLazy: ['nestedData']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyDtoWithNested::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('nestedData', $result);
        $this->assertIsArray($result['nestedData']['level1']);
        $this->assertIsArray($result['nestedData']['level1']['level2']);
        $this->assertIsArray($result['nestedData']['level1']['level2']['address']);
        $this->assertSame($street, $result['nestedData']['level1']['level2']['address']['street']);
        $this->assertSame($name, $result['nestedData']['level1']['level2']['name']);
    }

    public function test_lazy_property_respects_only_filter(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $options = new SerializationOptions()
            ->withIncludeLazy(['fullName'])
            ->withOnly(['firstName', 'fullName']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyUserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('firstName', $result);
        $this->assertArrayHasKey('fullName', $result);
        $this->assertArrayNotHasKey('lastName', $result);
        $this->assertArrayNotHasKey('email', $result);
    }

    public function test_lazy_property_respects_except_filter(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $options = new SerializationOptions()
            ->withIncludeLazy(['fullName', 'initials'])
            ->withExcept(['initials']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create(LazyUserDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('fullName', $result);
        $this->assertArrayNotHasKey('initials', $result); // Excluded by 'except'
    }

    public function test_lazy_property_with_array_of_dtos(): void
    {
        $addresses = [
            new AddressDto('Street 1', 'City 1', 'Country 1'),
            new AddressDto('Street 2', 'City 2', 'Country 2'),
        ];

        $testDto = new class($addresses) extends Dto implements ComputesLazyProperties
        {
            /**
             * @param  array<AddressDto>  $addresses
             */
            public function __construct(
                public array $addresses,
            ) {}

            public function computeLazyProperties(): array
            {
                return [
                    'addressList' => fn () => $this->addresses,
                ];
            }
        };

        $dto = new $testDto($addresses);

        $options = new SerializationOptions(includeLazy: ['addressList']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create($testDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('addressList', $result);
        $this->assertIsArray($result['addressList']);
        $this->assertCount(2, $result['addressList']);
        $this->assertIsArray($result['addressList'][0]);
        $this->assertSame('Street 1', $result['addressList'][0]['street']);
        $this->assertSame('Street 2', $result['addressList'][1]['street']);
    }

    public function test_lazy_property_handles_null_values(): void
    {
        $testDto = new class extends Dto implements ComputesLazyProperties
        {
            public function __construct(
                public ?string $name = null,
            ) {}

            public function computeLazyProperties(): array
            {
                return [
                    'nullValue' => null,
                    'closureReturningNull' => static fn () => null,
                ];
            }
        };

        $dto = new $testDto;

        $options = new SerializationOptions(includeLazy: ['nullValue', 'closureReturningNull']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create($testDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertArrayHasKey('nullValue', $result);
        $this->assertNull($result['nullValue']);
        $this->assertArrayHasKey('closureReturningNull', $result);
        $this->assertNull($result['closureReturningNull']);
    }

    public function test_lazy_property_with_scalar_values(): void
    {
        $testDto = new class extends Dto implements ComputesLazyProperties
        {
            public function __construct(
                public string $name = 'test',
            ) {}

            public function computeLazyProperties(): array
            {
                return [
                    'stringValue' => 'lazy string',
                    'intValue' => static fn () => 42,
                    'floatValue' => static fn () => 3.14,
                    'boolValue' => static fn () => true,
                ];
            }
        };

        $dto = new $testDto;

        $options = new SerializationOptions(includeLazy: []);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create($testDto::class);
        $result = $this->normalizer->normalize($dto, $meta, $context);

        $this->assertSame('lazy string', $result['stringValue']);
        $this->assertSame(42, $result['intValue']);
        $this->assertSame(3.14, $result['floatValue']);
        $this->assertTrue($result['boolValue']);
    }

    public function test_multiple_normalize_calls_with_same_dto(): void
    {
        $testDto = new class extends Dto implements ComputesLazyProperties
        {
            private int $callCount = 0;

            public function __construct(
                public string $name = 'test',
            ) {}

            public function computeLazyProperties(): array
            {
                $this->callCount++;

                return [
                    'lazy' => 'value',
                ];
            }

            public function getCallCount(): int
            {
                return $this->callCount;
            }
        };

        $dto = new $testDto;

        $options = new SerializationOptions(includeLazy: ['lazy']);
        $context = new Context(serializationOptions: $options);

        $meta = $this->metaFactory->create($testDto::class);

        // First call
        $this->normalizer->normalize($dto, $meta, $context);
        $this->assertSame(1, $dto->getCallCount());

        // Second call - should call computeLazyProperties again (new normalization run)
        $this->normalizer->normalize($dto, $meta, $context);
        $this->assertSame(2, $dto->getCallCount());
    }
}
