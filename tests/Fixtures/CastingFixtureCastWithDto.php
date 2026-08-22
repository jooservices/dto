<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Core\Dto;

final class CastingFixtureCastWithDto extends Dto
{
    public function __construct(
        #[CastWith(CastingFixtureUppercaseCaster::class)]
        public readonly string $label,
    ) {
    }
}
