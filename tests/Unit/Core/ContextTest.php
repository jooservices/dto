<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Hydration\Naming\SnakeCaseStrategy;
use JOOservices\Dto\Tests\TestCase;

final class ContextTest extends TestCase
{
    public function test_default_values(): void
    {
        $context = new Context;

        $this->assertNull($context->namingStrategy);
        $this->assertFalse($context->validationEnabled);
        $this->assertNull($context->serializationOptions);
        $this->assertSame('full', $context->transformerMode);
        $this->assertSame([], $context->customData);
    }

    public function test_with_naming_strategy(): void
    {
        $context = new Context;
        $strategy = new SnakeCaseStrategy;

        $newContext = $context->withNamingStrategy($strategy);

        $this->assertNotSame($context, $newContext);
        $this->assertNull($context->namingStrategy);
        $this->assertSame($strategy, $newContext->namingStrategy);
    }

    public function test_with_validation_enabled(): void
    {
        $context = new Context;

        $newContext = $context->withValidationEnabled(true);

        $this->assertFalse($context->validationEnabled);
        $this->assertTrue($newContext->validationEnabled);
    }

    public function test_with_serialization_options(): void
    {
        $context = new Context;
        $options = new SerializationOptions(only: ['name']);

        $newContext = $context->withSerializationOptions($options);

        $this->assertNull($context->serializationOptions);
        $this->assertSame($options, $newContext->serializationOptions);
    }

    public function test_with_transformer_mode(): void
    {
        $context = new Context;

        $newContext = $context->withTransformerMode('minimal');

        $this->assertSame('full', $context->transformerMode);
        $this->assertSame('minimal', $newContext->transformerMode);
    }

    public function test_with_custom_data(): void
    {
        $context = new Context;
        $customKey = $this->faker->word();
        $customValue = $this->faker->sentence();

        $newContext = $context->withCustomData([$customKey => $customValue]);

        $this->assertSame([], $context->customData);
        $this->assertSame($customValue, $newContext->customData[$customKey]);
    }

    public function test_with_custom_data_merges(): void
    {
        $key1 = $this->faker->unique()->word();
        $key2 = $this->faker->unique()->word();
        $value1 = $this->faker->sentence();
        $value2 = $this->faker->sentence();

        $context = new Context(customData: [$key1 => $value1]);
        $newContext = $context->withCustomData([$key2 => $value2]);

        $this->assertSame($value1, $newContext->customData[$key1]);
        $this->assertSame($value2, $newContext->customData[$key2]);
    }

    public function test_get_custom(): void
    {
        $key = $this->faker->word();
        $value = $this->faker->sentence();
        $context = new Context(customData: [$key => $value]);

        $this->assertSame($value, $context->getCustom($key));
    }

    public function test_get_custom_with_default(): void
    {
        $context = new Context;
        $default = $this->faker->sentence();

        $this->assertSame($default, $context->getCustom('nonexistent', $default));
    }

    public function test_has_custom(): void
    {
        $key = $this->faker->word();
        $context = new Context(customData: [$key => $this->faker->sentence()]);

        $this->assertTrue($context->hasCustom($key));
        $this->assertFalse($context->hasCustom('nonexistent'));
    }

    public function test_get_serialization_options(): void
    {
        $context = new Context;

        $options = $context->getSerializationOptions();

        $this->assertInstanceOf(SerializationOptions::class, $options);
    }

    public function test_get_serialization_options_returns_provided(): void
    {
        $options = new SerializationOptions(maxDepth: 5);
        $context = new Context(serializationOptions: $options);

        $this->assertSame($options, $context->getSerializationOptions());
    }

    public function test_is_full_transformer_mode(): void
    {
        $fullContext = new Context;
        $minimalContext = new Context(transformerMode: 'minimal');

        $this->assertTrue($fullContext->isFullTransformerMode());
        $this->assertFalse($minimalContext->isFullTransformerMode());
    }

    public function test_immutability(): void
    {
        $original = new Context;

        $modified = $original
            ->withNamingStrategy(new SnakeCaseStrategy)
            ->withValidationEnabled(true)
            ->withTransformerMode('minimal');

        $this->assertNull($original->namingStrategy);
        $this->assertFalse($original->validationEnabled);
        $this->assertSame('full', $original->transformerMode);

        $this->assertNotNull($modified->namingStrategy);
        $this->assertTrue($modified->validationEnabled);
        $this->assertSame('minimal', $modified->transformerMode);
    }

    public function test_with_cast_mode(): void
    {
        $context = new Context;
        $this->assertSame('loose', $context->castMode);

        $strictContext = $context->withCastMode('strict');
        $this->assertSame('strict', $strictContext->castMode);

        $permissiveContext = $context->withCastMode('permissive');
        $this->assertSame('permissive', $permissiveContext->castMode);
    }

    public function test_is_strict_mode(): void
    {
        $looseContext = new Context;
        $strictContext = new Context(castMode: 'strict');
        $permissiveContext = new Context(castMode: 'permissive');

        $this->assertFalse($looseContext->isStrictMode());
        $this->assertTrue($strictContext->isStrictMode());
        $this->assertFalse($permissiveContext->isStrictMode());
    }

    public function test_is_permissive_mode(): void
    {
        $looseContext = new Context;
        $strictContext = new Context(castMode: 'strict');
        $permissiveContext = new Context(castMode: 'permissive');

        $this->assertFalse($looseContext->isPermissiveMode());
        $this->assertFalse($strictContext->isPermissiveMode());
        $this->assertTrue($permissiveContext->isPermissiveMode());
    }

    public function test_permissive_static_constructor(): void
    {
        $context = Context::permissive();

        $this->assertTrue($context->isPermissiveMode());
        $this->assertSame('permissive', $context->castMode);
    }

    public function test_permissive_context_is_immutable(): void
    {
        $permissive = Context::permissive();
        $modified = $permissive->withValidationEnabled(true);

        $this->assertTrue($permissive->isPermissiveMode());
        $this->assertTrue($modified->isPermissiveMode());
        $this->assertFalse($permissive->validationEnabled);
        $this->assertTrue($modified->validationEnabled);
    }
}
