<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Tests\TestCase;

final class AttributesTest extends TestCase
{
    public function test_map_from_attribute(): void
    {
        $key = $this->faker->word().'_'.$this->faker->word();
        $attribute = new MapFrom($key);

        $this->assertSame($key, $attribute->key);
    }

    public function test_cast_with_attribute(): void
    {
        $casterClass = 'App\\Casters\\'.$this->faker->word().'Caster';
        $options = ['format' => $this->faker->word()];

        $attribute = new CastWith($casterClass, $options);

        $this->assertSame($casterClass, $attribute->casterClass);
        $this->assertSame($options, $attribute->options);
    }

    public function test_cast_with_attribute_default_options(): void
    {
        $casterClass = 'App\\Casters\\CustomCaster';

        $attribute = new CastWith($casterClass);

        $this->assertSame($casterClass, $attribute->casterClass);
        $this->assertSame([], $attribute->options);
    }

    public function test_transform_with_attribute(): void
    {
        $transformerClass = 'App\\Transformers\\'.$this->faker->word().'Transformer';
        $options = ['format' => $this->faker->word()];

        $attribute = new TransformWith($transformerClass, $options);

        $this->assertSame($transformerClass, $attribute->transformerClass);
        $this->assertSame($options, $attribute->options);
    }

    public function test_transform_with_attribute_default_options(): void
    {
        $transformerClass = 'App\\Transformers\\CustomTransformer';

        $attribute = new TransformWith($transformerClass);

        $this->assertSame($transformerClass, $attribute->transformerClass);
        $this->assertSame([], $attribute->options);
    }

    public function test_hidden_attribute(): void
    {
        $attribute = new Hidden;

        $this->assertInstanceOf(Hidden::class, $attribute);
    }

    public function test_map_from_with_various_key_formats(): void
    {
        $snakeCase = new MapFrom('user_email_address');
        $this->assertSame('user_email_address', $snakeCase->key);

        $camelCase = new MapFrom('userEmailAddress');
        $this->assertSame('userEmailAddress', $camelCase->key);

        $withNumbers = new MapFrom('field_2_name');
        $this->assertSame('field_2_name', $withNumbers->key);
    }
}
