<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core\Traits;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Engine\EngineFactory;
use JOOservices\Dto\Exceptions\HydrationException;
use ReflectionClass;

trait MutatesData
{
    private static ?Engine $mutationEngine = null;

    public static function setMutationEngine(Engine $engine): void
    {
        self::$mutationEngine = $engine;
    }

    public static function resetMutationEngine(): void
    {
        self::$mutationEngine = null;
    }

    private static function getMutationEngine(): Engine
    {
        if (self::$mutationEngine === null) {
            self::$mutationEngine = new EngineFactory()->create();
        }

        return self::$mutationEngine;
    }

    /**
     * @param  array<string, mixed>  $patch
     */
    public function update(array $patch, ?Context $ctx = null): void
    {
        $reflection = new ReflectionClass($this);

        foreach ($patch as $property => $value) {
            if (! $reflection->hasProperty($property)) {
                throw new HydrationException(
                    message: "Property '{$property}' does not exist on ".static::class,
                    path: $property,
                );
            }

            $prop = $reflection->getProperty($property);

            if (! $prop->isPublic()) {
                throw new HydrationException(
                    message: "Property '{$property}' is not accessible on ".static::class,
                    path: $property,
                );
            }

            $prop->setValue($this, $value);
        }
    }

    public function set(string $property, mixed $value): void
    {
        $this->update([$property => $value]);
    }
}
