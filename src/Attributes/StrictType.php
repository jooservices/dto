<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Force strict type checking for this property, overriding Context castMode.
 *
 * When applied, the property will not allow type coercion regardless of
 * the global cast mode setting in Context.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class StrictType
{
    public function __construct(
        public string $message = 'Type mismatch',
    ) {}
}
