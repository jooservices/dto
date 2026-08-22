<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureTypedArrayDto extends Dto
{
    public function __construct(
        /** @var array<int> */
        public readonly array $numbers,
    ) {
    }
}
