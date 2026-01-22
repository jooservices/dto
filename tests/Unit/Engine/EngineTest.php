<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Engine;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizers\ArrayInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\JsonInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\ObjectInputNormalizer;
use JOOservices\Dto\Normalization\Normalizer;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class EngineTest extends TestCase
{
    private Engine $engine;

    private SpyMetaFactory $metaFactory;

    private SpyHydrator $hydrator;

    private SpyNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metaFactory = new SpyMetaFactory;
        $this->hydrator = new SpyHydrator;
        $this->normalizer = new SpyNormalizer;

        $this->engine = new Engine($this->metaFactory, $this->hydrator, $this->normalizer);
    }

    public function test_add_input_normalizer(): void
    {
        $normalizer = new ArrayInputNormalizer;

        $result = $this->engine->addInputNormalizer($normalizer);

        $this->assertSame($this->engine, $result);
    }

    public function test_hydrate_with_array_input(): void
    {
        $this->engine->addInputNormalizer(new ArrayInputNormalizer);

        $data = [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $expectedDto = new SimpleDto(
            name: $data['name'],
            age: $data['age'],
            email: null,
        );
        $this->hydrator->hydrateResult = $expectedDto;

        $result = $this->engine->hydrate(SimpleDto::class, $data);

        $this->assertInstanceOf(SimpleDto::class, $result);
        $this->assertSame(1, $this->metaFactory->createCalls);
        $this->assertSame(SimpleDto::class, $this->metaFactory->lastClassName);
        $this->assertSame(1, $this->hydrator->hydrateCalls);
        $this->assertSame($data, $this->hydrator->lastData);
    }

    public function test_hydrate_with_json_input(): void
    {
        $this->engine->addInputNormalizer(new JsonInputNormalizer);

        $data = [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ];
        $json = json_encode($data, JSON_THROW_ON_ERROR);

        $expectedDto = new SimpleDto(
            name: $data['name'],
            age: $data['age'],
            email: null,
        );
        $this->hydrator->hydrateResult = $expectedDto;

        $result = $this->engine->hydrate(SimpleDto::class, $json);

        $this->assertInstanceOf(SimpleDto::class, $result);
        $this->assertSame(1, $this->metaFactory->createCalls);
        $this->assertSame(1, $this->hydrator->hydrateCalls);
        $this->assertSame($data, $this->hydrator->lastData);
    }

    public function test_hydrate_with_object_input(): void
    {
        $this->engine->addInputNormalizer(new ObjectInputNormalizer);

        $obj = new stdClass;
        $obj->name = $this->faker->name();
        $obj->age = $this->faker->numberBetween(18, 99);

        // Object normalizer converts object to array
        $expectedData = ['name' => $obj->name, 'age' => $obj->age];

        $expectedDto = new SimpleDto(
            name: $obj->name,
            age: $obj->age,
            email: null,
        );
        $this->hydrator->hydrateResult = $expectedDto;

        $result = $this->engine->hydrate(SimpleDto::class, $obj);

        $this->assertInstanceOf(SimpleDto::class, $result);
        $this->assertSame(1, $this->hydrator->hydrateCalls);
        $this->assertSame($expectedData, $this->hydrator->lastData);
    }

    public function test_hydrate_with_context(): void
    {
        $this->engine->addInputNormalizer(new ArrayInputNormalizer);

        $data = ['name' => $this->faker->name()];
        $context = new Context;

        $expectedDto = new SimpleDto(name: $data['name'], age: 25, email: null);
        $this->hydrator->hydrateResult = $expectedDto;

        $this->engine->hydrate(SimpleDto::class, $data, $context);

        $this->assertSame(1, $this->hydrator->hydrateCalls);
        $this->assertSame($context, $this->hydrator->lastContext);
    }

    public function test_hydrate_throws_exception_for_unsupported_input(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Cannot normalize input of type');

        $this->engine->hydrate(SimpleDto::class, 12345);
    }

    public function test_normalize(): void
    {
        $dto = new SimpleDto(
            name: $this->faker->name(),
            age: $this->faker->numberBetween(18, 99),
            email: null,
        );

        $expected = ['name' => $dto->name, 'age' => $dto->age];
        $this->normalizer->normalizeResult = $expected;

        $result = $this->engine->normalize($dto);

        $this->assertSame($expected, $result);
        $this->assertSame(1, $this->metaFactory->createCalls);
        $this->assertSame(SimpleDto::class, $this->metaFactory->lastClassName);
        $this->assertSame(1, $this->normalizer->normalizeCalls);
        $this->assertSame($dto, $this->normalizer->lastObject);
    }

    public function test_normalize_with_context(): void
    {
        $dto = new SimpleDto(name: $this->faker->name(), age: 25, email: null);
        $context = new Context;
        $this->normalizer->normalizeResult = [];

        $this->engine->normalize($dto, $context);

        $this->assertSame(1, $this->normalizer->normalizeCalls);
        $this->assertSame($context, $this->normalizer->lastContext);
    }

    public function test_normalize_to_json(): void
    {
        $dto = new SimpleDto(
            name: $this->faker->name(),
            age: $this->faker->numberBetween(18, 99),
            email: null,
        );

        $expected = ['name' => $dto->name, 'age' => $dto->age];
        $this->normalizer->normalizeResult = $expected;

        $json = $this->engine->normalizeToJson($dto);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        $this->assertSame($expected, $decoded);
    }

    public function test_normalize_to_json_with_flags(): void
    {
        $dto = new SimpleDto(name: 'Test', age: 25, email: null);
        $this->normalizer->normalizeResult = ['name' => 'Test'];

        $json = $this->engine->normalizeToJson($dto, null, JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $json);
    }
}

class SpyMetaFactory implements \JOOservices\Dto\Meta\MetaFactoryInterface
{
    public int $createCalls = 0;

    public ?string $lastClassName = null;

    public function create(string $className): \JOOservices\Dto\Meta\ClassMeta
    {
        $this->createCalls++;
        $this->lastClassName = $className;

        return new \JOOservices\Dto\Meta\ClassMeta(
            className: $className,
            isReadonly: false,
            properties: [],
            constructorParams: [],
            attributes: [],
        );
    }
}

class SpyHydrator implements \JOOservices\Dto\Hydration\HydratorInterface
{
    public int $hydrateCalls = 0;

    public ?array $lastData = null;

    public ?Context $lastContext = null;

    public object $hydrateResult;

    public function hydrate(\JOOservices\Dto\Meta\ClassMeta $meta, array $data, ?Context $ctx = null): object
    {
        $this->hydrateCalls++;
        $this->lastData = $data;
        $this->lastContext = $ctx;

        return $this->hydrateResult ?? new stdClass;
    }
}

class SpyNormalizer implements \JOOservices\Dto\Normalization\NormalizerInterface
{
    public int $normalizeCalls = 0;

    public ?object $lastObject = null;

    public ?Context $lastContext = null;

    public array $normalizeResult = [];

    public function normalize(object $instance, ?\JOOservices\Dto\Meta\ClassMeta $meta = null, ?Context $ctx = null): array
    {
        $this->normalizeCalls++;
        $this->lastObject = $instance;
        $this->lastContext = $ctx;

        return $this->normalizeResult;
    }
}
