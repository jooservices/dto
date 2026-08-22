<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Hydration\Pipeline\StripTags;

/**
 * Exercises the `[class-string, options]` tuple form of a Pipeline step.
 */
final class AttrFixturePipelineWithOptions extends Dto
{
    public function __construct(
        #[Pipeline(steps: [[StripTags::class, ['<b>']]])]
        public readonly string $html,
    ) {
    }
}
