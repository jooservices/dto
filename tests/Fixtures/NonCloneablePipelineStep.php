<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Hydration\PipelineStepInterface;

/**
 * Pipeline step that forbids cloning for cache fallback tests.
 */
final class NonCloneablePipelineStep implements PipelineStepInterface
{
    private function __clone(): void
    {
    }

    public function handle(mixed $value): mixed
    {
        return $value;
    }
}
