<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core\Concerns;

use ArrayAccess;
use BadMethodCallException;
use JOOservices\Dto\Core\Concerns\SupportsArrayAccess;
use JOOservices\Dto\Core\Dto;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

final class SupportsArrayAccessTest extends TestCase
{
    public function test_offset_exists(): void
    {
        $dto = TestDtoWithArrayAccess::from(['name' => 'John', 'age' => 30]);

        $this->assertTrue(isset($dto['name']));
        $this->assertTrue(isset($dto['age']));
        $this->assertFalse(isset($dto['nonexistent']));
    }

    public function test_offset_get(): void
    {
        $dto = TestDtoWithArrayAccess::from(['name' => 'John', 'age' => 30]);

        $this->assertSame('John', $dto['name']);
        $this->assertSame(30, $dto['age']);
    }

    public function test_offset_get_throws_for_nonexistent_property(): void
    {
        $dto = TestDtoWithArrayAccess::from(['name' => 'John', 'age' => 30]);

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("Property 'nonexistent' does not exist");

        $value = $dto['nonexistent'];
    }

    public function test_offset_set_throws(): void
    {
        $dto = TestDtoWithArrayAccess::from(['name' => 'John', 'age' => 30]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('DTOs are immutable');

        $dto['name'] = 'Jane';
    }

    public function test_offset_unset_throws(): void
    {
        $dto = TestDtoWithArrayAccess::from(['name' => 'John', 'age' => 30]);

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('DTOs are immutable');

        unset($dto['name']);
    }

    public function test_implements_array_access(): void
    {
        $dto = TestDtoWithArrayAccess::from(['name' => 'John', 'age' => 30]);

        $this->assertInstanceOf(ArrayAccess::class, $dto);
    }
}

final class TestDtoWithArrayAccess extends Dto implements ArrayAccess
{
    use SupportsArrayAccess;

    public function __construct(
        public string $name,
        public int $age,
    ) {}
}
