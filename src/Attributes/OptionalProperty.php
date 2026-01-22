<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Marks a property as optional (for documentation purposes).
 *
 * Use with Optional<T> type hint to indicate that a property may be absent.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class OptionalProperty
{
    public function __construct() {}
}
