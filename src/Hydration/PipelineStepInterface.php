<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

/**
 * Single pipeline step applied during hydration.
 */
interface PipelineStepInterface
{
    public function handle(mixed $value): mixed;
}
