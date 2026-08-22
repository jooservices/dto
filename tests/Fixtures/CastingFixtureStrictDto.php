<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\StrictType;
use JOOservices\Dto\Core\Dto;

final class CastingFixtureStrictDto extends Dto
{
    public function __construct(
        #[StrictType]
        public readonly int $value,
        #[StrictType(message: 'Custom strict failure')]
        public readonly int $other = 0,
    ) {
    }
}
