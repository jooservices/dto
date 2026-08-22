<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Core\Dto;

final class CastingFixtureCastWithMissingClassDto extends Dto
{
    public function __construct(
        /** @phpstan-ignore argument.type */
        #[CastWith('JOOservices\Dto\Tests\Fixtures\CastingFixtureNoSuchCasterClass')]
        public readonly string $label,
    ) {
    }
}
