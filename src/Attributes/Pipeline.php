<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Apply a pipeline of transformations to a property value during hydration.
 *
 * Pipeline steps are executed in order before casting and validation.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Pipeline
{
    /**
     * @param  array<class-string<\JOOservices\Dto\Hydration\PipelineStepInterface>>  $steps
     */
    public function __construct(
        public array $steps,
    ) {}
}
