<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Benchmark;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\BenchmarkTenFieldDto;
use LogicException;
use PhpBench\Attributes\BeforeMethods;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\Revs;
use ReflectionException;

#[BeforeMethods('setUp')]
final class DtoOperationsBench
{
    private ?BenchmarkTenFieldDto $instance = null;

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return [
            'field01' => 'alpha',
            'field02' => 'beta',
            'field03' => 'gamma',
            'field04' => 42,
            'field05' => 7,
            'field06' => true,
            'field07' => 'optional',
            'field08' => 99,
            'field09' => 'nine',
            'field10' => 10,
        ];
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function setUp(): void
    {
        $this->instance = BenchmarkTenFieldDto::fromArray($this->payload());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchFrom(): void
    {
        BenchmarkTenFieldDto::fromArray($this->payload());
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws ReflectionException
     */
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchToArray(): void
    {
        $this->requireInstance()->toArray();
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws ReflectionException
     * @throws ValidationException
     */
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchValidate(): void
    {
        $this->requireInstance()->validate(Context::defaultValidation());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws LogicException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    #[Revs(1000)]
    #[Iterations(5)]
    public function benchWith(): void
    {
        $this->requireInstance()->with(field04: 43);
    }

    /**
     * @throws LogicException
     */
    private function requireInstance(): BenchmarkTenFieldDto
    {
        if ($this->instance === null) {
            throw new LogicException('Benchmark setUp() was not called.');
        }

        return $this->instance;
    }
}
