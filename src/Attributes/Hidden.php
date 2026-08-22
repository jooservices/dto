<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Excludes a property from serialization (`toArray()` / `toJson()`). Internal state
 * views (`with()`, `clone()`, `hash()`, `diff()`, `equals()`) still include it.
 *
 * External hydration (`from()`, `fromRequest()`) ignores source keys for hidden
 * properties to prevent mass-assignment; set hidden values via constructor, `with()`,
 * or server-side code only.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Hidden
{
}
