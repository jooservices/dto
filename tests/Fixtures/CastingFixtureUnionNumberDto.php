<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureUnionNumberDto extends Dto
{
    public function __construct(
        public readonly int | float $value,
    ) {
    }
}
