<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureStringDto extends Dto
{
    public function __construct(
        public readonly string $value,
        public readonly ?string $optionalValue = null,
    ) {
    }
}
