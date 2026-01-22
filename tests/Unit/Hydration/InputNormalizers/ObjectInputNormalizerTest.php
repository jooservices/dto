<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use DateTime;
use JOOservices\Dto\Hydration\InputNormalizers\ObjectInputNormalizer;
use JOOservices\Dto\Tests\TestCase;
use stdClass;

final class ObjectInputNormalizerTest extends TestCase
{
    private ObjectInputNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->normalizer = new ObjectInputNormalizer;
    }

    public function test_supports_object(): void
    {
        $this->assertTrue($this->normalizer->supports(new stdClass));
        $this->assertTrue($this->normalizer->supports(new DateTime));
    }

    public function test_does_not_support_non_object(): void
    {
        $this->assertFalse($this->normalizer->supports('string'));
        $this->assertFalse($this->normalizer->supports(123));
        $this->assertFalse($this->normalizer->supports([]));
        $this->assertFalse($this->normalizer->supports(null));
    }

    public function test_normalize_std_class(): void
    {
        $obj = new stdClass;
        $obj->name = 'John';
        $obj->age = 30;

        $result = $this->normalizer->normalize($obj);

        $this->assertIsArray($result);
        $this->assertSame('John', $result['name']);
        $this->assertSame(30, $result['age']);
    }

    public function test_normalize_custom_object(): void
    {
        $obj = new class
        {
            public string $name = 'Jane';

            public int $age = 25;
        };

        $result = $this->normalizer->normalize($obj);

        $this->assertIsArray($result);
        $this->assertSame('Jane', $result['name']);
        $this->assertSame(25, $result['age']);
    }

    public function test_normalize_returns_empty_array_for_non_object(): void
    {
        $result = $this->normalizer->normalize('not an object');

        $this->assertSame([], $result);
    }

    public function test_normalize_only_includes_public_properties(): void
    {
        $obj = new class
        {
            public string $public = 'visible';

            protected string $protected = 'hidden';

            private string $private = 'hidden';
        };

        $result = $this->normalizer->normalize($obj);

        $this->assertArrayHasKey('public', $result);
        $this->assertArrayNotHasKey('protected', $result);
        $this->assertArrayNotHasKey('private', $result);
    }

    public function test_normalize_handles_empty_std_class(): void
    {
        $obj = new stdClass;

        $result = $this->normalizer->normalize($obj);

        $this->assertSame([], $result);
    }

    public function test_normalize_handles_object_with_nested_values(): void
    {
        $obj = new stdClass;
        $obj->user = new stdClass;
        $obj->user->name = 'John';
        $obj->settings = ['theme' => 'dark'];

        $result = $this->normalizer->normalize($obj);

        $this->assertIsArray($result);
        $this->assertInstanceOf(stdClass::class, $result['user']);
        $this->assertIsArray($result['settings']);
    }

    public function test_normalize_handles_object_with_null_values(): void
    {
        $obj = new stdClass;
        $obj->name = 'John';
        $obj->email = null;

        $result = $this->normalizer->normalize($obj);

        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertNull($result['email']);
    }

    public function test_normalize_excludes_static_properties(): void
    {
        $obj = new class
        {
            public static string $static = 'static';

            public string $instance = 'instance';
        };

        $result = $this->normalizer->normalize($obj);

        $this->assertArrayHasKey('instance', $result);
        $this->assertArrayNotHasKey('static', $result);
    }
}
