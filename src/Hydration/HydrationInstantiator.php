<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\ClassMeta;

final class HydrationInstantiator
{
    /**
     * @param  array<string, mixed>  $args
     *
     * @throws HydrationException
     */
    public function instantiate(ClassMeta $meta, array $args): object
    {
        $ordered = array_map(
            static fn(string $name): mixed => $args[$name],
            $meta->ctorParams,
        );

        $className = $meta->className;
        $instance = new $className(...$ordered);
        if (! $instance instanceof $className) {
            throw new HydrationException(
                message: 'Hydration did not produce an instance of the requested class.',
                expectedType: $className,
            );
        }

        return $instance;
    }
}
