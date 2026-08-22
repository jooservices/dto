<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

/**
 * A `mixed`-typed holder bypasses casting entirely, so it is useful to smuggle
 * raw values (INF/NaN, plain objects, other DTOs) straight into normalization.
 */
final class CoreFixtureMixedHolderDto extends Dto
{
    public function __construct(
        public readonly mixed $payload,
    ) {
    }
}
