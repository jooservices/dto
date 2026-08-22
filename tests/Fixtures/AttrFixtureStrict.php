<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\StrictType;
use JOOservices\Dto\Core\Dto;

final class AttrFixtureStrict extends Dto
{
    public function __construct(
        #[StrictType(message: 'count must be a native int')]
        public readonly int $count,
    ) {
    }
}
