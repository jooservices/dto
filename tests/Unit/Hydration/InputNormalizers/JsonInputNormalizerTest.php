<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizers\JsonInputNormalizer;
use JOOservices\Dto\Tests\TestCase;

final class JsonInputNormalizerTest extends TestCase
{
    private JsonInputNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new JsonInputNormalizer;
    }

    public function test_supports_valid_json_object(): void
    {
        $json = '{"name": "test"}';

        $this->assertTrue($this->normalizer->supports($json));
    }

    public function test_supports_valid_json_array(): void
    {
        $json = '[1, 2, 3]';

        $this->assertTrue($this->normalizer->supports($json));
    }

    public function test_supports_json_with_whitespace(): void
    {
        $json = '   {"name": "test"}';

        $this->assertTrue($this->normalizer->supports($json));
    }

    public function test_does_not_support_non_string(): void
    {
        $this->assertFalse($this->normalizer->supports(123));
        $this->assertFalse($this->normalizer->supports(['array']));
        $this->assertFalse($this->normalizer->supports(null));
    }

    public function test_does_not_support_plain_string(): void
    {
        $this->assertFalse($this->normalizer->supports('plain string'));
        $this->assertFalse($this->normalizer->supports('name=value'));
    }

    public function test_normalize_json_object(): void
    {
        $name = $this->faker->name();
        $age = $this->faker->numberBetween(18, 99);
        $json = json_encode(['name' => $name, 'age' => $age], JSON_THROW_ON_ERROR);

        $result = $this->normalizer->normalize($json);

        $this->assertSame($name, $result['name']);
        $this->assertSame($age, $result['age']);
    }

    public function test_normalize_json_array(): void
    {
        $items = [$this->faker->word(), $this->faker->word()];
        $json = json_encode($items, JSON_THROW_ON_ERROR);

        $result = $this->normalizer->normalize($json);

        $this->assertSame($items, $result);
    }

    public function test_normalize_throws_for_invalid_json(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Invalid JSON input');

        $this->normalizer->normalize('{invalid json}');
    }

    public function test_normalize_throws_for_non_array_json(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('JSON must decode to an array or object');

        $this->normalizer->normalize('"just a string"');
    }

    public function test_normalize_returns_empty_array_for_non_string(): void
    {
        $result = $this->normalizer->normalize(123);

        $this->assertSame([], $result);
    }

    public function test_normalize_nested_json(): void
    {
        $json = json_encode([
            'user' => [
                'name' => $this->faker->name(),
                'address' => [
                    'city' => $this->faker->city(),
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $result = $this->normalizer->normalize($json);

        $this->assertIsArray($result['user']);
        $this->assertIsArray($result['user']['address']);
    }

    public function test_normalize_with_unicode_characters(): void
    {
        $json = '{"name": "日本語", "emoji": "🎉"}';

        $result = $this->normalizer->normalize($json);

        $this->assertSame('日本語', $result['name']);
        $this->assertSame('🎉', $result['emoji']);
    }

    public function test_normalize_empty_object(): void
    {
        $result = $this->normalizer->normalize('{}');

        $this->assertSame([], $result);
    }

    public function test_normalize_empty_array(): void
    {
        $result = $this->normalizer->normalize('[]');

        $this->assertSame([], $result);
    }
}
