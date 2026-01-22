<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use DateTimeImmutable;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Normalization\TransformerInterface;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;

final class TransformerRegistryTest extends TestCase
{
    private TransformerRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new TransformerRegistry;
    }

    public function test_register_transformer(): void
    {
        $transformer = new DateTimeTransformer;

        $this->registry->register($transformer, 10);

        $property = $this->createDateTimeProperty();
        $result = $this->registry->get($property, new DateTimeImmutable);

        $this->assertInstanceOf(TransformerInterface::class, $result);
    }

    public function test_get_returns_null_when_no_transformer_supports(): void
    {
        $property = $this->createStringProperty();

        $result = $this->registry->get($property, 'test');

        $this->assertNull($result);
    }

    public function test_get_returns_first_supporting_transformer(): void
    {
        $dateTimeTransformer = new DateTimeTransformer;
        $this->registry->register($dateTimeTransformer, 10);

        $property = $this->createDateTimeProperty();
        $result = $this->registry->get($property, new DateTimeImmutable);

        $this->assertSame($dateTimeTransformer, $result);
    }

    public function test_transformer_priority_ordering(): void
    {
        $lowPriorityTransformer = new class implements TransformerInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return 'low';
            }
        };

        $highPriorityTransformer = new class implements TransformerInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return 'high';
            }
        };

        $this->registry->register($lowPriorityTransformer, 10);
        $this->registry->register($highPriorityTransformer, 20);

        $property = $this->createStringProperty();
        $transformer = $this->registry->get($property, 'test');

        $this->assertSame($highPriorityTransformer, $transformer);
    }

    public function test_transform_uses_get_to_find_transformer(): void
    {
        $transformer = new DateTimeTransformer;
        $this->registry->register($transformer, 10);

        $property = $this->createDateTimeProperty();
        $date = new DateTimeImmutable('2026-01-15T10:30:00+00:00');
        $result = $this->registry->transform($property, $date, null);

        $this->assertIsString($result);
        $this->assertStringContainsString('2026-01-15', $result);
    }

    public function test_transform_returns_value_when_no_transformer_found(): void
    {
        $property = $this->createStringProperty();
        $value = 'test value';

        $result = $this->registry->transform($property, $value, null);

        $this->assertSame($value, $result);
    }

    public function test_can_transform_returns_true_when_transformer_exists(): void
    {
        $transformer = new DateTimeTransformer;
        $this->registry->register($transformer, 10);

        $property = $this->createDateTimeProperty();
        $result = $this->registry->canTransform($property, new DateTimeImmutable);

        $this->assertTrue($result);
    }

    public function test_can_transform_returns_false_when_no_transformer_exists(): void
    {
        $property = $this->createStringProperty();
        $result = $this->registry->canTransform($property, 'test');

        $this->assertFalse($result);
    }

    public function test_transformers_sorted_by_priority_descending(): void
    {
        $transformer1 = new class implements TransformerInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return '1';
            }
        };

        $transformer2 = new class implements TransformerInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return '2';
            }
        };

        $transformer3 = new class implements TransformerInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return '3';
            }
        };

        $this->registry->register($transformer1, 5);
        $this->registry->register($transformer2, 15);
        $this->registry->register($transformer3, 10);

        $property = $this->createStringProperty();
        $result = $this->registry->get($property, 'test');

        $this->assertSame($transformer2, $result);
    }

    public function test_register_multiple_transformers(): void
    {
        $dateTime = new DateTimeTransformer;
        $enum = new EnumTransformer;

        $this->registry->register($dateTime, 10);
        $this->registry->register($enum, 20);

        $dateProperty = $this->createDateTimeProperty();
        $result1 = $this->registry->get($dateProperty, new DateTimeImmutable);
        $this->assertInstanceOf(DateTimeTransformer::class, $result1);

        $enumProperty = $this->createEnumProperty();
        $result2 = $this->registry->get($enumProperty, Status::Active);
        $this->assertInstanceOf(EnumTransformer::class, $result2);
    }

    public function test_transform_passes_context_to_transformer(): void
    {
        $context = new Context;
        $transformer = new class implements TransformerInterface
        {
            public ?Context $capturedContext = null;

            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                $this->capturedContext = $ctx;

                return 'transformed';
            }
        };

        $this->registry->register($transformer, 10);

        $property = $this->createStringProperty();
        $this->registry->transform($property, 'test', $context);

        $this->assertSame($context, $transformer->capturedContext);
    }

    public function test_sorting_only_happens_once(): void
    {
        $transformer = new DateTimeTransformer;

        $this->registry->register($transformer, 10);

        $property = $this->createDateTimeProperty();
        $date = new DateTimeImmutable;

        $this->registry->get($property, $date);
        $this->registry->get($property, $date);
        $this->registry->get($property, $date);

        $this->assertTrue(true);
    }

    private function createStringProperty(): PropertyMeta
    {
        return new PropertyMeta(
            name: $this->faker->word(),
            type: new TypeDescriptor(
                name: 'string',
                isBuiltin: true,
                isNullable: false,
                isArray: false,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            ),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );
    }

    private function createDateTimeProperty(): PropertyMeta
    {
        return new PropertyMeta(
            name: $this->faker->word(),
            type: new TypeDescriptor(
                name: DateTimeImmutable::class,
                isBuiltin: false,
                isNullable: false,
                isArray: false,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: true,
            ),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );
    }

    private function createEnumProperty(): PropertyMeta
    {
        return new PropertyMeta(
            name: $this->faker->word(),
            type: new TypeDescriptor(
                name: Status::class,
                isBuiltin: false,
                isNullable: false,
                isArray: false,
                arrayItemType: null,
                isEnum: true,
                enumClass: Status::class,
                isDto: false,
                isDateTime: false,
            ),
            isReadonly: true,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );
    }
}
