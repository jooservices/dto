<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use Closure;

/**
 * Opt-in interface for lazy/derived properties during serialization.
 *
 * Closures in the returned map are deferred until included via
 * {@see SerializationOptions::$includeLazy}. Eager values are returned as-is.
 */
interface ComputesLazyProperties
{
    /**
     * @return array<string, Closure():mixed|mixed>
     */
    public function computeLazyProperties(): array;
}
