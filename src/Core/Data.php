<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionClass;
use ReflectionException;

/**
 * Mutable Data counterpart to {@see Dto}.
 *
 * Properties must not be readonly so {@see set()} / {@see update()} can mutate.
 */
abstract class Data extends AbstractDto
{
    /**
     * @param  array<string, mixed>  $values  Property-name keys
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function set(array $values, ?Context $ctx = null): static
    {
        $updated = $ctx === null ? $this->with($values) : $this->with($values, $ctx);
        $reflection = new ReflectionClass($this);

        foreach (array_keys($values) as $name) {
            $property = $reflection->getProperty($name);
            $property->setValue($this, $property->getValue($updated));
        }

        return $this;
    }

    /**
     * @param  array<string, mixed>  $values  Property-name keys
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function update(array $values, ?Context $ctx = null): static
    {
        return $this->set($values, $ctx);
    }
}
