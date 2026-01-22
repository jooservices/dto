<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use DateTimeImmutable;
use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Casting\Casters\DateTimeCaster;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Tests\Fixtures\SimpleDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;

final class MetaFactoryTest extends TestCase
{
    private MetaFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();

        $cache = new MemoryMetaCache;
        $this->factory = new MetaFactory($cache);
    }

    public function test_create_builds_class_meta(): void
    {
        $meta = $this->factory->create(SimpleDto::class);

        $this->assertInstanceOf(ClassMeta::class, $meta);
        $this->assertSame(SimpleDto::class, $meta->className);
    }

    public function test_create_uses_cache(): void
    {
        $meta1 = $this->factory->create(SimpleDto::class);
        $meta2 = $this->factory->create(SimpleDto::class);

        $this->assertSame($meta1, $meta2);
    }

    public function test_create_builds_properties_metadata(): void
    {
        $meta = $this->factory->create(SimpleDto::class);

        $this->assertNotEmpty($meta->properties);
        $this->assertArrayHasKey('name', $meta->properties);
        $this->assertArrayHasKey('age', $meta->properties);
        $this->assertArrayHasKey('email', $meta->properties);
    }

    public function test_create_builds_constructor_params(): void
    {
        $meta = $this->factory->create(SimpleDto::class);

        $this->assertNotEmpty($meta->constructorParams);
        $this->assertContains('name', $meta->constructorParams);
        $this->assertContains('age', $meta->constructorParams);
        $this->assertContains('email', $meta->constructorParams);
    }

    public function test_create_extracts_map_from_attribute(): void
    {
        $meta = $this->factory->create(UserDto::class);
        $emailProperty = $meta->getProperty('email');

        $this->assertNotNull($emailProperty);
        $this->assertSame('email_address', $emailProperty->mapFrom);
    }

    public function test_create_extracts_hidden_attribute(): void
    {
        $meta = $this->factory->create(UserDto::class);
        $passwordProperty = $meta->getProperty('passwordHash');

        $this->assertNotNull($passwordProperty);
        $this->assertTrue($passwordProperty->isHidden);
    }

    public function test_create_extracts_default_values(): void
    {
        $meta = $this->factory->create(SimpleDto::class);
        $emailProperty = $meta->getProperty('email');

        $this->assertNotNull($emailProperty);
        $this->assertTrue($emailProperty->hasDefault);
        $this->assertNull($emailProperty->defaultValue);
    }

    public function test_create_detects_readonly_class(): void
    {
        if (PHP_VERSION_ID < 80200) {
            $this->markTestSkipped('Readonly classes require PHP 8.2+');
        }

        $testClass = new readonly class {};

        $meta = $this->factory->create($testClass::class);

        $this->assertTrue($meta->isReadonly);
    }

    public function test_create_skips_static_properties(): void
    {
        $testClass = new class
        {
            public static string $staticProp = 'static';

            public string $instanceProp = 'instance';
        };

        $meta = $this->factory->create($testClass::class);

        $this->assertArrayNotHasKey('staticProp', $meta->properties);
        $this->assertArrayHasKey('instanceProp', $meta->properties);
    }

    public function test_create_extracts_property_attributes(): void
    {
        $meta = $this->factory->create(UserDto::class);
        $emailProperty = $meta->getProperty('email');

        $this->assertNotNull($emailProperty);
        $this->assertNotEmpty($emailProperty->attributes);

        $hasMapFrom = false;

        foreach ($emailProperty->attributes as $attribute) {
            if ($attribute instanceof MapFrom) {
                $hasMapFrom = true;

                break;
            }
        }

        $this->assertTrue($hasMapFrom);
    }

    public function test_create_handles_class_with_no_constructor(): void
    {
        $testClass = new class
        {
            public string $prop = 'value';
        };

        $meta = $this->factory->create($testClass::class);

        $this->assertEmpty($meta->constructorParams);
        $this->assertNotEmpty($meta->properties);
    }

    public function test_create_handles_promoted_property_defaults(): void
    {
        $testClass = new class('test', 42)
        {
            public function __construct(
                public readonly string $name = 'default',
                public readonly int $value = 0,
            ) {}
        };

        $meta = $this->factory->create($testClass::class);
        $nameProperty = $meta->getProperty('name');
        $valueProperty = $meta->getProperty('value');

        $this->assertNotNull($nameProperty);
        $this->assertTrue($nameProperty->hasDefault);
        $this->assertSame('default', $nameProperty->defaultValue);

        $this->assertNotNull($valueProperty);
        $this->assertTrue($valueProperty->hasDefault);
        $this->assertSame(0, $valueProperty->defaultValue);
    }

    public function test_create_extracts_caster_class(): void
    {
        $testClass = new class(new DateTimeImmutable)
        {
            public function __construct(
                #[CastWith(DateTimeCaster::class)]
                public readonly DateTimeImmutable $date,
            ) {}
        };

        $meta = $this->factory->create($testClass::class);
        $dateProperty = $meta->getProperty('date');

        $this->assertNotNull($dateProperty);
        $this->assertSame(DateTimeCaster::class, $dateProperty->casterClass);
    }

    public function test_create_extracts_transformer_class(): void
    {
        $testClass = new class(new DateTimeImmutable)
        {
            public function __construct(
                #[TransformWith(DateTimeTransformer::class)]
                public readonly DateTimeImmutable $date,
            ) {}
        };

        $meta = $this->factory->create($testClass::class);
        $dateProperty = $meta->getProperty('date');

        $this->assertNotNull($dateProperty);
        $this->assertSame(DateTimeTransformer::class, $dateProperty->transformerClass);
    }

    public function test_create_with_readonly_property(): void
    {
        $meta = $this->factory->create(SimpleDto::class);
        $nameProperty = $meta->getProperty('name');

        $this->assertNotNull($nameProperty);
        $this->assertTrue($nameProperty->isReadonly);
    }
}
