<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Core\Dto;

final class AttrFixtureTransformed extends Dto
{
    public function __construct(
        #[TransformWith(AttrFixtureUpperOutputTransformer::class)]
        public readonly string $label,
    ) {
    }
}
