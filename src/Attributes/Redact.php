<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Marks a property as secret: serialization (`toArray()`/`toJson()`) and cast/validation
 * exception payloads emit `$with` instead of the real value. Unlike `#[Hidden]`, the key
 * is kept in the output — only the value is replaced. Internal state views (`with()`,
 * `clone()`, `hash()`, `diff()`, `equals()`) still see the real value; only the output
 * boundary is redacted.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Redact
{
    public function __construct(
        public string $with = '***REDACTED***',
    ) {
    }
}
