<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization\Transformers;

use DateTimeImmutable;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Tests\TestCase;

final class DateTimeTransformerTest extends TestCase
{
    public function test_supports_date_time_immutable(): void
    {
        $transformer = new DateTimeTransformer;
        $property = $this->createProperty();
        $dateTime = new DateTimeImmutable;

        $this->assertTrue($transformer->supports($property, $dateTime));
    }

    public function test_does_not_support_non_date_time(): void
    {
        $transformer = new DateTimeTransformer;
        $property = $this->createProperty();

        $this->assertFalse($transformer->supports($property, 'string'));
        $this->assertFalse($transformer->supports($property, 123));
        $this->assertFalse($transformer->supports($property, null));
    }

    public function test_transform_with_default_format(): void
    {
        $transformer = new DateTimeTransformer;
        $property = $this->createProperty();
        $dateTime = new DateTimeImmutable('2026-01-15T10:30:00+00:00');

        $result = $transformer->transform($property, $dateTime, null);

        $this->assertStringContainsString('2026-01-15', $result);
        $this->assertStringContainsString('10:30:00', $result);
    }

    public function test_transform_with_custom_format(): void
    {
        $transformer = new DateTimeTransformer('Y-m-d');
        $property = $this->createProperty();
        $dateTime = new DateTimeImmutable('2026-01-15T10:30:00+00:00');

        $result = $transformer->transform($property, $dateTime, null);

        $this->assertSame('2026-01-15', $result);
    }

    public function test_transform_with_different_formats(): void
    {
        $dateTime = new DateTimeImmutable('2026-01-15T10:30:00+00:00');
        $property = $this->createProperty();

        $isoTransformer = new DateTimeTransformer('Y-m-d\TH:i:sP');
        $this->assertStringContainsString('2026-01-15', $isoTransformer->transform($property, $dateTime, null));

        $usTransformer = new DateTimeTransformer('m/d/Y');
        $this->assertSame('01/15/2026', $usTransformer->transform($property, $dateTime, null));

        $euTransformer = new DateTimeTransformer('d.m.Y');
        $this->assertSame('15.01.2026', $euTransformer->transform($property, $dateTime, null));
    }

    public function test_transform_returns_empty_string_for_non_date_time(): void
    {
        $transformer = new DateTimeTransformer;
        $property = $this->createProperty();

        $result = $transformer->transform($property, 'not-a-datetime', null);

        $this->assertSame('', $result);
    }

    private function createProperty(): PropertyMeta
    {
        return new PropertyMeta(
            name: $this->faker->word(),
            type: TypeDescriptor::mixed(),
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
