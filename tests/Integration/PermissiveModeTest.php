<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration;

use DateTimeImmutable;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Tests\Fixtures\Priority;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;

final class PermissiveModeTest extends TestCase
{
    public function test_permissive_mode_converts_invalid_values_to_null(): void
    {
        $dto = PermissiveTestDto::from([
            'name' => 123,
            'age' => 'not-a-number',
            'score' => 'not-a-float',
            'active' => 'not-a-bool',
            'createdAt' => 'invalid-date',
            'status' => 'invalid-status',
            'priority' => 'InvalidPriority',
        ], Context::permissive());

        $this->assertSame('123', $dto->name);
        $this->assertNull($dto->age);
        $this->assertNull($dto->score);
        $this->assertNull($dto->active);
        $this->assertNull($dto->createdAt);
        $this->assertNull($dto->status);
        $this->assertNull($dto->priority);
    }

    public function test_permissive_mode_allows_valid_values(): void
    {
        $dto = PermissiveTestDto::from([
            'name' => 'John Doe',
            'age' => '25',
            'score' => '95.5',
            'active' => 'true',
            'createdAt' => '2024-01-15',
            'status' => 'active',
            'priority' => 'High',
        ], Context::permissive());

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame(25, $dto->age);
        $this->assertSame(95.5, $dto->score);
        $this->assertTrue($dto->active);
        $this->assertInstanceOf(DateTimeImmutable::class, $dto->createdAt);
        $this->assertSame('2024-01-15', $dto->createdAt->format('Y-m-d'));
        $this->assertSame(Status::Active, $dto->status);
        $this->assertSame(Priority::High, $dto->priority);
    }

    public function test_permissive_mode_with_nested_dtos(): void
    {
        $dto = NestedPermissiveDto::from([
            'title' => 'Test',
            'nested' => [
                'name' => 'John',
                'age' => 'invalid',
                'score' => 95.5,
                'active' => true,
                'createdAt' => '2024-01-15',
                'status' => 'active',
                'priority' => 'High',
            ],
        ], Context::permissive());

        $this->assertSame('Test', $dto->title);
        $this->assertSame('John', $dto->nested->name);
        $this->assertNull($dto->nested->age);
    }

    public function test_permissive_mode_with_array_of_scalars(): void
    {
        $dto = ArrayPermissiveDto::from([
            'numbers' => ['1', 'invalid', '3', '4.5'],
        ], Context::permissive());

        $this->assertSame(['1', 'invalid', '3', '4.5'], $dto->numbers);
    }

    public function test_permissive_mode_combines_with_other_context_features(): void
    {
        $dto = PermissiveTestDto::from([
            'name' => 'John',
            'age' => 'invalid',
            'score' => 95.5,
            'active' => true,
            'createdAt' => '2024-01-15',
            'status' => 'active',
            'priority' => 'High',
        ], Context::permissive()->withValidationEnabled(false));

        $this->assertNull($dto->age);
    }

    public function test_non_permissive_mode_throws_on_invalid_cast(): void
    {
        $this->expectException(\JOOservices\Dto\Exceptions\HydrationException::class);

        PermissiveTestDto::from([
            'name' => 'John',
            'age' => 'not-a-number',
            'score' => 95.5,
            'active' => true,
            'createdAt' => '2024-01-15',
            'status' => 'active',
            'priority' => 'High',
        ]);
    }
}

class PermissiveTestDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly ?int $age,
        public readonly ?float $score,
        public readonly ?bool $active,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?Status $status,
        public readonly ?Priority $priority,
    ) {}
}

class NestedPermissiveDto extends Dto
{
    public function __construct(
        public readonly string $title,
        public readonly PermissiveTestDto $nested,
    ) {}
}

class ArrayPermissiveDto extends Dto
{
    public function __construct(
        /** @var array<string> */
        public readonly array $numbers,
    ) {}
}
