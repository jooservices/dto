<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\ClassFromNestedDtoFactory;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureNotADto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureRecursiveDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class NestedDtoFactoryTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCreateHydratesFromArraySource(): void
    {
        $dto = (new ClassFromNestedDtoFactory())->create(UserDto::class, ['name' => 'Ann', 'age' => 20], null);

        self::assertInstanceOf(UserDto::class, $dto);
        self::assertSame('Ann', $dto->name);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCreateHydratesFromJsonStringSource(): void
    {
        $dto = (new ClassFromNestedDtoFactory())->create(UserDto::class, '{"name":"Ann","age":20}', null);

        self::assertInstanceOf(UserDto::class, $dto);
        self::assertSame(20, $dto->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCreateRejectsClassThatIsNotADto(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Nested type is not a DTO.');

        (new ClassFromNestedDtoFactory())->create(CoreFixtureNotADto::class, ['kind' => 'x'], null);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCreateRejectsSourceThatIsNeitherArrayObjectNorString(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Nested DTO source must be array, object, or JSON string.');

        (new ClassFromNestedDtoFactory())->create(UserDto::class, 123, null);
    }

    /**
     * C11: depth guard must reject nested DTO chains deeper than the default
     * limit, even though every individual level is otherwise perfectly valid.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDeeplyNestedChainBeyondDefaultDepthLimitThrows(): void
    {
        $payload = $this->buildNestedChain(34);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Hydration depth exceeded.');

        CoreFixtureRecursiveDto::fromArray($payload);
    }

    /**
     * A chain within the limit hydrates successfully end-to-end.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNestedChainWithinDepthLimitHydratesSuccessfully(): void
    {
        $payload = $this->buildNestedChain(3);

        $dto = CoreFixtureRecursiveDto::fromArray($payload);

        self::assertSame(0, $dto->level);
        self::assertNotNull($dto->child);
        self::assertSame(1, $dto->child->level);
        self::assertNotNull($dto->child->child);
        self::assertSame(2, $dto->child->child->level);
        self::assertNull($dto->child->child->child);
    }

    /**
     * @return array<string, mixed>
     */
    private function buildNestedChain(int $depth): array
    {
        $payload = ['level' => $depth - 1, 'child' => null];
        for ($level = $depth - 2; $level >= 0; $level--) {
            $payload = ['level' => $level, 'child' => $payload];
        }

        return $payload;
    }
}
