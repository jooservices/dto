<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureAssocMixedArrayDto extends Dto
{
    public function __construct(
        /** @var array<string, mixed> */
        public readonly array $meta = [],
    ) {
    }
}
