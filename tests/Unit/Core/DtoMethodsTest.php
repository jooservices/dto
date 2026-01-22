<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use InvalidArgumentException;
use JOOservices\Dto\Core\Dto;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DtoMethodsTest extends TestCase
{
    public function test_diff(): void
    {
        $dto1 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $dto2 = TestDtoForMethods::from(['name' => 'Jane', 'age' => 30, 'email' => 'jane@example.com']);

        $diff = $dto1->diff($dto2);

        $this->assertArrayHasKey('name', $diff);
        $this->assertArrayHasKey('email', $diff);
        $this->assertArrayNotHasKey('age', $diff);

        $this->assertSame('John', $diff['name']['old']);
        $this->assertSame('Jane', $diff['name']['new']);
    }

    public function test_diff_with_missing_properties(): void
    {
        $dto1 = TestDtoWithOptional::from(['name' => 'John', 'email' => 'john@example.com']);
        $dto2 = TestDtoWithOptional::from(['name' => 'John']);

        $diff = $dto1->diff($dto2);

        $this->assertArrayHasKey('email', $diff);
        $this->assertSame('john@example.com', $diff['email']['old']);
        $this->assertNull($diff['email']['new']);
    }

    public function test_diff_throws_on_different_types(): void
    {
        $dto1 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'test@example.com']);
        $dto2 = new class extends Dto
        {
            public string $name = 'test';
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only diff DTOs of the same type');

        $dto1->diff($dto2);
    }

    public function test_equals(): void
    {
        $dto1 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $dto2 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $dto3 = TestDtoForMethods::from(['name' => 'Jane', 'age' => 30, 'email' => 'john@example.com']);

        $this->assertTrue($dto1->equals($dto2));
        $this->assertFalse($dto1->equals($dto3));
    }

    public function test_hash(): void
    {
        $dto1 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $dto2 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $dto3 = TestDtoForMethods::from(['name' => 'Jane', 'age' => 30, 'email' => 'jane@example.com']);

        $hash1 = $dto1->hash();
        $hash2 = $dto2->hash();
        $hash3 = $dto3->hash();

        $this->assertSame($hash1, $hash2);
        $this->assertNotSame($hash1, $hash3);
        $this->assertIsString($hash1);
        $this->assertNotEmpty($hash1);
    }

    public function test_merge(): void
    {
        $dto1 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $dto2 = TestDtoForMethods::from(['name' => 'Jane', 'age' => 35, 'email' => 'jane@example.com']);

        $merged = $dto1->merge($dto2);

        $this->assertSame('Jane', $merged->name);
        $this->assertSame(35, $merged->age);
        $this->assertSame('jane@example.com', $merged->email);

        // Original unchanged
        $this->assertSame('John', $dto1->name);
    }

    public function test_merge_throws_on_different_types(): void
    {
        $dto1 = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'test@example.com']);
        $dto2 = new class extends Dto
        {
            public string $name = 'test';
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Can only merge DTOs of the same type');

        $dto1->merge($dto2);
    }

    public function test_clone(): void
    {
        $dto = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $cloned = $dto->clone();

        $this->assertNotSame($dto, $cloned);
        $this->assertTrue($dto->equals($cloned));
    }

    public function test_replicate(): void
    {
        $dto = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);
        $replicated = $dto->replicate();

        $this->assertNotSame($dto, $replicated);
        $this->assertTrue($dto->equals($replicated));
    }

    public function test_when_with_condition_true(): void
    {
        $dto = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);

        $result = $dto->when(true, ['extra' => 'data']);

        $this->assertSame(['extra' => 'data'], $result);
    }

    public function test_when_with_condition_false(): void
    {
        $dto = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);

        $result = $dto->when(false, ['extra' => 'data']);

        $this->assertSame([], $result);
    }

    public function test_when_with_callable(): void
    {
        $dto = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);

        $result = $dto->when(true, fn () => ['computed' => 'value']);

        $this->assertSame(['computed' => 'value'], $result);
    }

    public function test_unless(): void
    {
        $dto = TestDtoForMethods::from(['name' => 'John', 'age' => 30, 'email' => 'john@example.com']);

        $result1 = $dto->unless(false, ['extra' => 'data']);
        $result2 = $dto->unless(true, ['extra' => 'data']);

        $this->assertSame(['extra' => 'data'], $result1);
        $this->assertSame([], $result2);
    }

    public function test_transform_input_method_exists(): void
    {
        $reflection = new ReflectionClass(TestDtoWithTransformInput::class);

        $this->assertTrue($reflection->hasMethod('transformInput'));
        $this->assertTrue($reflection->getMethod('transformInput')->isStatic());
        $this->assertTrue($reflection->getMethod('transformInput')->isProtected());
    }

    public function test_after_hydration_method_exists(): void
    {
        $reflection = new ReflectionClass(TestDtoWithAfterHydration::class);

        $this->assertTrue($reflection->hasMethod('afterHydration'));
        $this->assertFalse($reflection->getMethod('afterHydration')->isStatic());
        $this->assertTrue($reflection->getMethod('afterHydration')->isProtected());
    }
}

final class TestDtoForMethods extends Dto
{
    public function __construct(
        public string $name,
        public int $age,
        public string $email,
    ) {}
}

final class TestDtoWithOptional extends Dto
{
    public function __construct(
        public string $name,
        public ?string $email = null,
    ) {}
}

final class TestDtoWithTransformInput extends Dto
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function transformInput(array $data): array
    {
        if (isset($data['name']) && is_string($data['name'])) {
            $data['name'] = strtoupper(trim($data['name']));
        }

        return $data;
    }
}

final class TestDtoWithAfterHydration extends Dto
{
    public function __construct(
        public string $name,
        public int $age,
    ) {}

    protected function afterHydration(): void
    {
        if ($this->age < 0) {
            throw new LogicException('Age cannot be negative');
        }
    }
}
