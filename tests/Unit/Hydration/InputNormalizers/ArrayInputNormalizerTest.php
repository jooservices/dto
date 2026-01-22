<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use JOOservices\Dto\Hydration\InputNormalizers\ArrayInputNormalizer;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class ArrayInputNormalizerTest extends TestCase
{
    private ArrayInputNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new ArrayInputNormalizer;
    }

    public function test_supports_array(): void
    {
        $this->assertTrue($this->normalizer->supports([]));
        $this->assertTrue($this->normalizer->supports(['key' => 'value']));
    }

    public function test_does_not_support_non_array(): void
    {
        $this->assertFalse($this->normalizer->supports('string'));
        $this->assertFalse($this->normalizer->supports(123));
        $this->assertFalse($this->normalizer->supports(new stdClass));
        $this->assertFalse($this->normalizer->supports(null));
    }

    public function test_normalize_returns_array(): void
    {
        $input = ['name' => 'John', 'age' => 30];

        $result = $this->normalizer->normalize($input);

        $this->assertSame($input, $result);
    }

    public function test_normalize_returns_empty_array_for_non_array(): void
    {
        $result = $this->normalizer->normalize('not an array');

        $this->assertSame([], $result);
    }

    public function test_normalize_preserves_array_structure(): void
    {
        $input = [
            'user' => [
                'name' => 'John',
                'email' => 'john@example.com',
            ],
            'settings' => [
                'theme' => 'dark',
            ],
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertSame($input, $result);
    }

    public function test_normalize_handles_empty_array(): void
    {
        $input = [];

        $result = $this->normalizer->normalize($input);

        $this->assertSame([], $result);
    }

    public function test_normalize_handles_numeric_keys(): void
    {
        $input = [0 => 'a', 1 => 'b', 2 => 'c'];

        $result = $this->normalizer->normalize($input);

        $this->assertSame($input, $result);
    }

    public function test_normalize_handles_mixed_keys(): void
    {
        $input = [
            'string_key' => 'value1',
            0 => 'value2',
            'another_key' => 'value3',
        ];

        $result = $this->normalizer->normalize($input);

        $this->assertSame($input, $result);
    }
}
