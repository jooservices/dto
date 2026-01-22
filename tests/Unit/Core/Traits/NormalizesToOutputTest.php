<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core\Traits;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Core\Traits\NormalizesToOutput;
use JOOservices\Dto\Tests\TestCase;

final class NormalizesToOutputTest extends TestCase
{
    protected function tearDown(): void
    {
        NormalizableTestClass::resetNormalizerEngine();

        parent::tearDown();
    }

    public function test_to_array(): void
    {
        $obj = new NormalizableTestClass('John', 30);

        $result = $obj->toArray();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('age', $result);
    }

    public function test_to_array_with_context(): void
    {
        $obj = new NormalizableTestClass('John', 30);
        $context = new Context(
            serializationOptions: new SerializationOptions(only: ['name']),
        );

        $result = $obj->toArray($context);

        $this->assertIsArray($result);
    }

    public function test_to_json(): void
    {
        $obj = new NormalizableTestClass('John', 30);

        $result = $obj->toJson();

        $this->assertIsString($result);
        $decoded = json_decode($result, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
    }

    public function test_to_json_with_flags(): void
    {
        $obj = new NormalizableTestClass('John', 30);

        $result = $obj->toJson(JSON_PRETTY_PRINT);

        $this->assertStringContainsString("\n", $result);
    }

    public function test_to_json_with_context(): void
    {
        $obj = new NormalizableTestClass('John', 30);
        $context = new Context(
            serializationOptions: new SerializationOptions(except: ['age']),
        );

        $result = $obj->toJson(0, $context);

        $this->assertIsString($result);
    }

    public function test_json_serialize(): void
    {
        $obj = new NormalizableTestClass('John', 30);

        $result = $obj->jsonSerialize();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('age', $result);
    }

    public function test_json_serialize_with_json_encode(): void
    {
        $obj = new NormalizableTestClass('John', 30);

        $json = json_encode($obj, JSON_THROW_ON_ERROR);

        $this->assertIsString($json);
        $decoded = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);
    }

    public function test_set_normalizer_engine(): void
    {
        $engine = new \JOOservices\Dto\Tests\Fixtures\SpyEngine;
        $engine->normalizeResult = ['name' => 'John', 'age' => 30];

        NormalizableTestClass::setNormalizerEngine($engine);

        $obj = new NormalizableTestClass('John', 30);
        $obj->toArray();

        $this->assertSame(1, $engine->normalizeCalls);
    }

    public function test_reset_normalizer_engine(): void
    {
        $engine = new \JOOservices\Dto\Tests\Fixtures\SpyEngine;

        NormalizableTestClass::setNormalizerEngine($engine);
        NormalizableTestClass::resetNormalizerEngine();

        $obj = new NormalizableTestClass('John', 30);
        $result = $obj->toArray();

        $this->assertIsArray($result);
    }

    public function test_engine_is_singleton(): void
    {
        $obj1 = new NormalizableTestClass('John', 30);
        $obj2 = new NormalizableTestClass('Jane', 25);

        $result1 = $obj1->toArray();
        $result2 = $obj2->toArray();

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
    }

    public function test_engine_is_lazily_created(): void
    {
        NormalizableTestClass::resetNormalizerEngine();

        $obj = new NormalizableTestClass('John', 30);
        $result = $obj->toArray();

        $this->assertIsArray($result);
    }

    public function test_to_json_with_all_parameters(): void
    {
        $obj = new NormalizableTestClass('John', 30);
        $context = new Context;

        $result = $obj->toJson(JSON_PRETTY_PRINT, $context);

        $this->assertIsString($result);
        $this->assertStringContainsString("\n", $result);
    }
}

final class NormalizableTestClass
{
    use NormalizesToOutput;

    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {}
}
