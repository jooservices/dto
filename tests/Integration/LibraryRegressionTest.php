<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration;

use InvalidArgumentException;
use JOOservices\Dto\Engine\EngineHolder;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Support\CoverageExercises;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use ReflectionException;
use RuntimeException;

/**
 * Cross-module regression scenarios executed only in the Integration suite.
 */
final class LibraryRegressionTest extends TestCase
{
    protected function tearDown(): void
    {
        EngineHolder::reset();

        parent::tearDown();
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws CastException
     * @throws ValidationException
     */
    public function testAbstractDtoAndDataScenarios(): void
    {
        CoverageExercises::runAbstractDtoAndDataScenarios($this->faker());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testContextAndSerializationOptionsScenarios(): void
    {
        CoverageExercises::runContextAndSerializationOptionsScenarios($this->faker());
    }

    /**
     * @throws RuntimeException
     */
    public function testOptionalScenarios(): void
    {
        CoverageExercises::runOptionalScenarios($this->faker());
    }

    public function testExceptionScenarios(): void
    {
        CoverageExercises::runExceptionScenarios($this->faker());
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testEngineScenarios(): void
    {
        CoverageExercises::runEngineScenarios($this->faker());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testEngineHolderAndFactoryScenarios(): void
    {
        CoverageExercises::runEngineHolderAndFactoryScenarios($this->faker());
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testMetaFactoryScenarios(): void
    {
        CoverageExercises::runMetaFactoryScenarios($this->faker());
    }

    public function testMemoryMetaCacheScenarios(): void
    {
        CoverageExercises::runMemoryMetaCacheScenarios($this->faker());
    }

    /**
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws \Random\RandomException
     * @throws RuntimeException
     */
    public function testFileMetaCacheScenarios(): void
    {
        CoverageExercises::runFileMetaCacheScenarios($this->faker());
    }

    public function testNoConstructorHydrationScenario(): void
    {
        CoverageExercises::runNoConstructorHydrationScenario($this->faker());
    }

    public function testTypeDescriptorScenarios(): void
    {
        CoverageExercises::runTypeDescriptorScenarios($this->faker());
    }
}
