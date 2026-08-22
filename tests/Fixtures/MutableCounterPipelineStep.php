<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Hydration\PipelineStepInterface;

/**
 * Mutable pipeline step for cache-isolation tests only.
 */
final class MutableCounterPipelineStep implements PipelineStepInterface
{
    private int $invocations = 0;

    public function handle(mixed $value): mixed
    {
        $this->invocations++;

        return $value;
    }

    public function invocations(): int
    {
        return $this->invocations;
    }
}
