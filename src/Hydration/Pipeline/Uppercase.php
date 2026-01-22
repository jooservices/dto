<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Pipeline;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Hydration\PipelineStepInterface;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Convert string values to uppercase.
 */
final readonly class Uppercase implements PipelineStepInterface
{
    public function process(mixed $value, PropertyMeta $property, ?Context $ctx): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return strtoupper($value);
    }
}
