<?php

declare(strict_types=1);

namespace JOOservices\Dto\Support;

use InvalidArgumentException;

/**
 * Instantiate a class with constructor-spread options (list or named).
 */
final class OptionedInstantiator
{
    /**
     * @param  class-string  $className
     * @param  array<int|string, mixed>  $options
     *
     * @throws InvalidArgumentException
     */
    public function make(string $className, array $options = []): object
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException('Class does not exist: ' . $className);
        }

        if ($options === []) {
            return new $className();
        }

        return new $className(...$options);
    }
}
