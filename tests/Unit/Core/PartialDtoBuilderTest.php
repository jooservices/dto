<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\PartialDtoBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class PartialDtoBuilderTest extends TestCase
{
    public function test_from_with_array(): void
    {
        $builder = new PartialDtoBuilder(TestDtoForPartial::class, ['name', 'email']);

        $dto = $builder->from([
            'name' => 'John',
            'email' => 'john@example.com',
            'age' => 30,
            'city' => 'NYC',
        ]);

        $this->assertInstanceOf(TestDtoForPartial::class, $dto);
        $this->assertSame('John', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertNull($dto->age); // Not in allowed fields
        $this->assertNull($dto->city); // Not in allowed fields
    }

    public function test_from_with_object(): void
    {
        $builder = new PartialDtoBuilder(TestDtoForPartial::class, ['name']);

        $source = (object) [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'age' => 25,
        ];

        $dto = $builder->from($source);

        $this->assertSame('Jane', $dto->name);
        $this->assertNull($dto->email);
        $this->assertNull($dto->age);
    }

    public function test_get_allowed_fields(): void
    {
        $fields = ['name', 'email'];
        $builder = new PartialDtoBuilder(TestDtoForPartial::class, $fields);

        $this->assertSame($fields, $builder->getAllowedFields());
    }

    public function test_get_dto_class(): void
    {
        $builder = new PartialDtoBuilder(TestDtoForPartial::class, ['name']);

        $this->assertSame(TestDtoForPartial::class, $builder->getDtoClass());
    }

    public function test_readonly_class(): void
    {
        $reflection = new ReflectionClass(PartialDtoBuilder::class);
        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
    }

    public function test_empty_allowed_fields(): void
    {
        $builder = new PartialDtoBuilder(TestDtoForPartial::class, []);

        $dto = $builder->from([
            'name' => 'John',
            'email' => 'john@example.com',
        ]);

        $this->assertNull($dto->name);
        $this->assertNull($dto->email);
    }

    public function test_nonexistent_fields_ignored(): void
    {
        $builder = new PartialDtoBuilder(TestDtoForPartial::class, ['name', 'nonexistent']);

        $dto = $builder->from([
            'name' => 'John',
            'nonexistent' => 'value',
        ]);

        $this->assertSame('John', $dto->name);
        // nonexistent field should be safely ignored
    }
}

final class TestDtoForPartial extends Dto
{
    public function __construct(
        public ?string $name = null,
        public ?string $email = null,
        public ?int $age = null,
        public ?string $city = null,
    ) {}
}
