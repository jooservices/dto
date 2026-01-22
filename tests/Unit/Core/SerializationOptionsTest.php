<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\TestCase;

final class SerializationOptionsTest extends TestCase
{
    public function test_default_values(): void
    {
        $options = new SerializationOptions;

        $this->assertNull($options->only);
        $this->assertNull($options->except);
        $this->assertSame(10, $options->maxDepth);
    }

    public function test_should_include_with_only_whitelist(): void
    {
        $options = new SerializationOptions(only: ['name', 'email']);

        $this->assertTrue($options->shouldInclude('name'));
        $this->assertTrue($options->shouldInclude('email'));
        $this->assertFalse($options->shouldInclude('age'));
    }

    public function test_should_include_with_except_blacklist(): void
    {
        $options = new SerializationOptions(except: ['password', 'secret']);

        $this->assertTrue($options->shouldInclude('name'));
        $this->assertTrue($options->shouldInclude('email'));
        $this->assertFalse($options->shouldInclude('password'));
        $this->assertFalse($options->shouldInclude('secret'));
    }

    public function test_should_include_with_no_restrictions(): void
    {
        $options = new SerializationOptions;

        $this->assertTrue($options->shouldInclude($this->faker->word()));
        $this->assertTrue($options->shouldInclude($this->faker->word()));
    }

    public function test_can_descend(): void
    {
        $options = new SerializationOptions(maxDepth: 5);

        $this->assertTrue($options->canDescend(0));
        $this->assertTrue($options->canDescend(4));
        $this->assertFalse($options->canDescend(5));
        $this->assertFalse($options->canDescend(10));
    }

    public function test_with_only(): void
    {
        $options = new SerializationOptions(except: ['old']);
        $newOptions = $options->withOnly(['name', 'age']);

        $this->assertNull($options->only);
        $this->assertSame(['name', 'age'], $newOptions->only);
        $this->assertNull($newOptions->except);
    }

    public function test_with_except(): void
    {
        $options = new SerializationOptions(only: ['old']);
        $newOptions = $options->withExcept(['password']);

        $this->assertSame(['old'], $options->only);
        $this->assertNull($newOptions->only);
        $this->assertSame(['password'], $newOptions->except);
    }

    public function test_with_max_depth(): void
    {
        $options = new SerializationOptions;
        $newOptions = $options->withMaxDepth(20);

        $this->assertSame(10, $options->maxDepth);
        $this->assertSame(20, $newOptions->maxDepth);
    }

    public function test_immutability(): void
    {
        $original = new SerializationOptions;

        $modified = $original
            ->withOnly(['name'])
            ->withMaxDepth(5);

        $this->assertNull($original->only);
        $this->assertSame(10, $original->maxDepth);

        $this->assertSame(['name'], $modified->only);
        $this->assertSame(5, $modified->maxDepth);
    }

    public function test_only_prioritized_over_except(): void
    {
        $options = new SerializationOptions(
            only: ['name', 'email'],
            except: ['name'],
        );

        $this->assertTrue($options->shouldInclude('name'));
        $this->assertTrue($options->shouldInclude('email'));
        $this->assertFalse($options->shouldInclude('age'));
    }

    public function test_can_descend_with_zero_depth(): void
    {
        $options = new SerializationOptions(maxDepth: 0);

        $this->assertFalse($options->canDescend(0));
    }

    public function test_preserves_max_depth_on_with_only(): void
    {
        $options = new SerializationOptions(maxDepth: 15);
        $newOptions = $options->withOnly(['name']);

        $this->assertSame(15, $newOptions->maxDepth);
    }

    public function test_preserves_max_depth_on_with_except(): void
    {
        $options = new SerializationOptions(maxDepth: 15);
        $newOptions = $options->withExcept(['password']);

        $this->assertSame(15, $newOptions->maxDepth);
    }

    public function test_default_include_lazy(): void
    {
        $options = new SerializationOptions;

        $this->assertNull($options->includeLazy);
    }

    public function test_should_include_lazy_with_null(): void
    {
        $options = new SerializationOptions(includeLazy: null);

        $this->assertFalse($options->shouldIncludeLazy('avatar'));
        $this->assertFalse($options->shouldIncludeLazy('stats'));
    }

    public function test_should_include_lazy_with_empty_array(): void
    {
        $options = new SerializationOptions(includeLazy: []);

        $this->assertTrue($options->shouldIncludeLazy('avatar'));
        $this->assertTrue($options->shouldIncludeLazy('stats'));
        $this->assertTrue($options->shouldIncludeLazy($this->faker->word()));
    }

    public function test_should_include_lazy_with_specific(): void
    {
        $options = new SerializationOptions(includeLazy: ['avatar', 'fullName']);

        $this->assertTrue($options->shouldIncludeLazy('avatar'));
        $this->assertTrue($options->shouldIncludeLazy('fullName'));
        $this->assertFalse($options->shouldIncludeLazy('stats'));
        $this->assertFalse($options->shouldIncludeLazy('other'));
    }

    public function test_with_include_lazy(): void
    {
        $options = new SerializationOptions;
        $newOptions = $options->withIncludeLazy(['avatar', 'stats']);

        $this->assertNull($options->includeLazy);
        $this->assertSame(['avatar', 'stats'], $newOptions->includeLazy);
    }

    public function test_with_include_lazy_preserves_other_options(): void
    {
        $options = new SerializationOptions(
            only: ['name', 'email'],
            maxDepth: 15,
        );
        $newOptions = $options->withIncludeLazy(['avatar']);

        $this->assertSame(['name', 'email'], $newOptions->only);
        $this->assertSame(15, $newOptions->maxDepth);
        $this->assertSame(['avatar'], $newOptions->includeLazy);
    }

    public function test_with_include_lazy_can_set_null(): void
    {
        $options = new SerializationOptions(includeLazy: ['avatar']);
        $newOptions = $options->withIncludeLazy(null);

        $this->assertSame(['avatar'], $options->includeLazy);
        $this->assertNull($newOptions->includeLazy);
    }

    public function test_with_include_lazy_can_set_empty_array(): void
    {
        $options = new SerializationOptions;
        $newOptions = $options->withIncludeLazy([]);

        $this->assertSame([], $newOptions->includeLazy);
        $this->assertTrue($newOptions->shouldIncludeLazy('anything'));
    }

    public function test_preserves_include_lazy_on_with_only(): void
    {
        $options = new SerializationOptions(includeLazy: ['avatar']);
        $newOptions = $options->withOnly(['name']);

        $this->assertSame(['avatar'], $newOptions->includeLazy);
    }

    public function test_preserves_include_lazy_on_with_except(): void
    {
        $options = new SerializationOptions(includeLazy: ['avatar']);
        $newOptions = $options->withExcept(['password']);

        $this->assertSame(['avatar'], $newOptions->includeLazy);
    }

    public function test_preserves_include_lazy_on_with_max_depth(): void
    {
        $options = new SerializationOptions(includeLazy: ['avatar']);
        $newOptions = $options->withMaxDepth(20);

        $this->assertSame(['avatar'], $newOptions->includeLazy);
    }

    public function test_default_wrap(): void
    {
        $options = new SerializationOptions;

        $this->assertNull($options->wrap);
    }

    public function test_wrap_with_key(): void
    {
        $options = new SerializationOptions(wrap: 'data');

        $this->assertSame('data', $options->wrap);
    }

    public function test_with_wrap(): void
    {
        $options = new SerializationOptions;
        $newOptions = $options->withWrap('users');

        $this->assertNull($options->wrap);
        $this->assertSame('users', $newOptions->wrap);
    }

    public function test_with_wrap_preserves_other_options(): void
    {
        $options = new SerializationOptions(
            only: ['name', 'email'],
            maxDepth: 15,
            includeLazy: ['avatar'],
        );
        $newOptions = $options->withWrap('data');

        $this->assertSame(['name', 'email'], $newOptions->only);
        $this->assertSame(15, $newOptions->maxDepth);
        $this->assertSame(['avatar'], $newOptions->includeLazy);
        $this->assertSame('data', $newOptions->wrap);
    }

    public function test_with_wrap_can_set_null(): void
    {
        $options = new SerializationOptions(wrap: 'data');
        $newOptions = $options->withWrap(null);

        $this->assertSame('data', $options->wrap);
        $this->assertNull($newOptions->wrap);
    }

    public function test_preserves_wrap_on_with_only(): void
    {
        $options = new SerializationOptions(wrap: 'data');
        $newOptions = $options->withOnly(['name']);

        $this->assertSame('data', $newOptions->wrap);
    }

    public function test_preserves_wrap_on_with_except(): void
    {
        $options = new SerializationOptions(wrap: 'data');
        $newOptions = $options->withExcept(['password']);

        $this->assertSame('data', $newOptions->wrap);
    }

    public function test_preserves_wrap_on_with_max_depth(): void
    {
        $options = new SerializationOptions(wrap: 'data');
        $newOptions = $options->withMaxDepth(20);

        $this->assertSame('data', $newOptions->wrap);
    }

    public function test_preserves_wrap_on_with_include_lazy(): void
    {
        $options = new SerializationOptions(wrap: 'data');
        $newOptions = $options->withIncludeLazy(['avatar']);

        $this->assertSame('data', $newOptions->wrap);
    }

    public function test_wrap_immutability(): void
    {
        $original = new SerializationOptions(wrap: 'data');
        $modified = $original->withWrap('users');

        $this->assertSame('data', $original->wrap);
        $this->assertSame('users', $modified->wrap);
    }
}
