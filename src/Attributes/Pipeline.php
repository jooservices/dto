<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Property hydration pipeline. Each step is a class-string or
 * `[class-string, options-array]` for constructor-spread options.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Pipeline
{
    /**
     * @param  list<class-string|array{0: class-string, 1?: array<int|string, mixed>}>  $steps
     */
    public function __construct(
        public array $steps,
    ) {
    }
}
