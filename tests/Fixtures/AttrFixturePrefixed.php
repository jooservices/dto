<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Core\Dto;

final class AttrFixturePrefixed extends Dto
{
    public function __construct(
        #[CastWith(AttrFixturePrefixCaster::class, options: ['PRE-'])]
        public readonly string $value,
    ) {
    }
}
