<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Hydration\Pipeline\TrimStrings;
use JOOservices\Dto\Hydration\Pipeline\Uppercase;

final class AttrFixturePipeline extends Dto
{
    public function __construct(
        #[Pipeline(steps: [TrimStrings::class, Uppercase::class])]
        public readonly string $code,
    ) {
    }
}
