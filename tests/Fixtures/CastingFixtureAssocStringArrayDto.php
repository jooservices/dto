<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureAssocStringArrayDto extends Dto
{
    public function __construct(
        /** @var array<string, string> */
        public readonly array $avatarUrls = [],
    ) {
    }
}
