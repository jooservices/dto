<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Core\Dto;

/**
 * A DefaultFrom-decorated property with no PHP default and no nullability: when the fallback
 * (env, here) also comes up empty, hydration must fail loudly instead of silently defaulting.
 */
final class AttrFixtureDefaultFromRequired extends Dto
{
    public function __construct(
        #[DefaultFrom(env: 'ATTR_FIXTURE_REQUIRED_DEFAULT_FROM_ENV')]
        public readonly string $token,
    ) {
    }
}
