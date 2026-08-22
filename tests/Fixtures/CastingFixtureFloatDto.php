<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureFloatDto extends Dto
{
    public function __construct(
        public readonly float $value,
        public readonly ?float $optionalValue = null,
    ) {
    }
}
