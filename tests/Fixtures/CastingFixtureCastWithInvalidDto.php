<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Core\Dto;

final class CastingFixtureCastWithInvalidDto extends Dto
{
    public function __construct(
        #[CastWith(CastingFixtureNotACaster::class)]
        public readonly string $label,
    ) {
    }
}
