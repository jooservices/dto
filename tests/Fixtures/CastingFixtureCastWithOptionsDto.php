<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Core\Dto;

final class CastingFixtureCastWithOptionsDto extends Dto
{
    public function __construct(
        #[CastWith(CastingFixturePrefixCaster::class, options: ['prefix' => 'X-'])]
        public readonly string $label,
    ) {
    }
}
