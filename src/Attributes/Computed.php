<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Marks a method as a computed property.
 *
 * Computed properties are methods that are automatically included in serialization.
 * They can optionally be cached to avoid repeated computation.
 */
#[Attribute(Attribute::TARGET_METHOD)]
final readonly class Computed
{
    public function __construct(
        public bool $cached = true,
        public bool $includeInSerialization = true,
    ) {}
}
