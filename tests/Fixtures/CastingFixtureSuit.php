<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

/**
 * A pure (non-backed) enum, used to exercise EnumCaster's path for
 * enums that cannot be resolved from a scalar value.
 */
enum CastingFixtureSuit
{
    case Hearts;
    case Spades;
}
