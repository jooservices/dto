<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting\Casters;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use JOOservices\Dto\Casting\Casters\DateTimeCaster;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;

final class DateTimeCasterTest extends TestCase
{
    private DateTimeCaster $caster;

    protected function setUp(): void
    {
        parent::setUp();
        $this->caster = new DateTimeCaster;
    }

    public function test_supports_date_time_immutable(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);

        $this->assertTrue($this->caster->supports($property, '2026-01-15T10:30:00+00:00'));
        $this->assertTrue($this->caster->supports($property, new DateTimeImmutable));
        $this->assertTrue($this->caster->supports($property, time()));
    }

    public function test_supports_date_time(): void
    {
        $property = $this->createDateTimeProperty(DateTime::class);

        $this->assertTrue($this->caster->supports($property, '2026-01-15'));
    }

    public function test_supports_date_time_interface(): void
    {
        $property = $this->createDateTimeProperty(DateTimeInterface::class);

        $this->assertTrue($this->caster->supports($property, '2026-01-15'));
    }

    public function test_does_not_support_non_date_time_type(): void
    {
        $type = new TypeDescriptor(
            name: 'string',
            isBuiltin: true,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );

        $property = new PropertyMeta(
            name: $this->faker->word(),
            type: $type,
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

        $this->assertFalse($this->caster->supports($property, '2026-01-15'));
    }

    public function test_cast_from_iso8601_string(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $dateString = '2026-01-15T10:30:00+00:00';

        $result = $this->caster->cast($property, $dateString, null);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2026-01-15', $result->format('Y-m-d'));
        $this->assertSame('10:30:00', $result->format('H:i:s'));
    }

    public function test_cast_from_timestamp(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $timestamp = 1705320600;

        $result = $this->caster->cast($property, $timestamp, null);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function test_cast_from_date_time_interface(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $original = new DateTime('2026-01-15');

        $result = $this->caster->cast($property, $original, null);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2026-01-15', $result->format('Y-m-d'));
    }

    public function test_cast_to_date_time(): void
    {
        $property = $this->createDateTimeProperty(DateTime::class);

        $result = $this->caster->cast($property, '2026-01-15', null);

        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_cast_from_various_date_formats(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);

        $result1 = $this->caster->cast($property, '2026-01-15', null);
        $this->assertInstanceOf(DateTimeImmutable::class, $result1);

        $result2 = $this->caster->cast($property, 'January 15, 2026', null);
        $this->assertInstanceOf(DateTimeImmutable::class, $result2);

        $result3 = $this->caster->cast($property, '15-01-2026', null);
        $this->assertInstanceOf(DateTimeImmutable::class, $result3);
    }

    public function test_cast_throws_for_invalid_date_string(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'not-a-date', null);
    }

    public function test_cast_throws_for_invalid_type(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);

        $this->expectException(CastException::class);
        $this->caster->cast($property, ['array'], null);
    }

    public function test_cast_preserves_date_time_immutable_type(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $original = new DateTimeImmutable('2026-01-15');

        $result = $this->caster->cast($property, $original, null);

        $this->assertSame($original, $result);
    }

    public function test_cast_with_custom_format(): void
    {
        $caster = new DateTimeCaster('d/m/Y');
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);

        $result = $caster->cast($property, '15/01/2026', null);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2026-01-15', $result->format('Y-m-d'));
    }

    public function test_cast_from_negative_timestamp(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $timestamp = -86400;

        $result = $this->caster->cast($property, $timestamp, null);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('1969-12-31', $result->format('Y-m-d'));
    }

    public function test_permissive_mode_returns_null_on_invalid_datetime_string(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 'invalid-date-string', $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_returns_null_on_invalid_type(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, ['array'], $context);

        $this->assertNull($result);
    }

    public function test_permissive_mode_allows_valid_datetime_casts(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, '2024-01-15', $context);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
        $this->assertSame('2024-01-15', $result->format('Y-m-d'));
    }

    public function test_permissive_mode_allows_timestamp_casts(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $context = \JOOservices\Dto\Core\Context::permissive();

        $result = $this->caster->cast($property, 1705276800, $context);

        $this->assertInstanceOf(DateTimeImmutable::class, $result);
    }

    public function test_permissive_mode_allows_datetime_instance(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $context = \JOOservices\Dto\Core\Context::permissive();
        $dateTime = new DateTimeImmutable('2024-01-15');

        $result = $this->caster->cast($property, $dateTime, $context);

        $this->assertSame($dateTime, $result);
    }

    public function test_strict_mode_still_throws_on_invalid_datetime(): void
    {
        $property = $this->createDateTimeProperty(DateTimeImmutable::class);
        $context = new \JOOservices\Dto\Core\Context(castMode: 'strict');

        $this->expectException(CastException::class);
        $this->caster->cast($property, 'invalid-date', $context);
    }

    private function createDateTimeProperty(string $className): PropertyMeta
    {
        $type = new TypeDescriptor(
            name: $className,
            isBuiltin: false,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: true,
        );

        return new PropertyMeta(
            name: $this->faker->word(),
            type: $type,
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
