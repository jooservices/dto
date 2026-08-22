<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Pipeline;

use JOOservices\Dto\Hydration\PipelineStepInterface;

final class Uppercase implements PipelineStepInterface
{
    public function handle(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return strtoupper($value);
    }
}
