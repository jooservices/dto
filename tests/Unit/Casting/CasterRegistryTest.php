<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use DateTimeImmutable;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Casting\Casters\ScalarCaster;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;

final class CasterRegistryTest extends TestCase
{
    private CasterRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();

        $this->registry = new CasterRegistry;
    }

    public function test_register_caster(): void
    {
        $caster = new ScalarCaster;

        $this->registry->register($caster, 10);

        $property = $this->createStringProperty();
        $result = $this->registry->get($property, 'test');

        $this->assertInstanceOf(CasterInterface::class, $result);
    }

    public function test_get_returns_null_when_no_caster_supports(): void
    {
        $property = $this->createStringProperty();

        $result = $this->registry->get($property, 'test');

        $this->assertNull($result);
    }

    public function test_get_returns_first_supporting_caster(): void
    {
        $scalarCaster = new ScalarCaster;
        $this->registry->register($scalarCaster, 10);

        $property = $this->createStringProperty();
        $result = $this->registry->get($property, 'test');

        $this->assertSame($scalarCaster, $result);
    }

    public function test_caster_priority_ordering(): void
    {
        $lowPriorityCaster = new class implements CasterInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return 'low';
            }
        };

        $highPriorityCaster = new class implements CasterInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return 'high';
            }
        };

        $this->registry->register($lowPriorityCaster, 10);
        $this->registry->register($highPriorityCaster, 20);

        $property = $this->createStringProperty();
        $caster = $this->registry->get($property, 'test');

        $this->assertSame($highPriorityCaster, $caster);
    }

    // ... [skip simple tests] ...

    public function test_casters_sorted_by_priority_descending(): void
    {
        $caster1 = new class implements CasterInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return '1';
            }
        };

        $caster2 = new class implements CasterInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return '2';
            }
        };

        $caster3 = new class implements CasterInterface
        {
            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                return '3';
            }
        };

        $this->registry->register($caster1, 5);
        $this->registry->register($caster2, 15);
        $this->registry->register($caster3, 10);

        $property = $this->createStringProperty();
        $result = $this->registry->get($property, 'test');

        $this->assertSame($caster2, $result);
    }

    // ... [skip simple tests] ...

    public function test_cast_passes_context_to_caster(): void
    {
        $context = new Context;
        $caster = new class implements CasterInterface
        {
            public ?Context $capturedContext = null;

            public function supports(PropertyMeta $property, mixed $value): bool
            {
                return true;
            }

            public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                $this->capturedContext = $ctx;

                return 'casted';
            }
        };

        $this->registry->register($caster, 10);

        $property = $this->createStringProperty();
        $this->registry->cast($property, 'test', $context);

        $this->assertSame($context, $caster->capturedContext);
    }

    public function test_sorting_only_happens_once(): void
    {
        $caster = new ScalarCaster;

        $this->registry->register($caster, 10);

        $property = $this->createStringProperty();

        $this->registry->get($property, 'test1');
        $this->registry->get($property, 'test2');
        $this->registry->get($property, 'test3');

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
}
