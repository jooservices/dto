<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Pipeline;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Hydration\PipelineStepInterface;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Trim whitespace from string values.
 */
final readonly class TrimStrings implements PipelineStepInterface
{
    public function __construct(
        private string $characters = " \n\r\t\v\0",
    ) {}

    public function process(mixed $value, PropertyMeta $property, ?Context $ctx): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return trim($value, $this->characters);
    }
}
