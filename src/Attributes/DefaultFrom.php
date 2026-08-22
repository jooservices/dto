<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * Fallback when the mapped input key is missing. Order: env, then static method.
 * Laravel `config()` is not used.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class DefaultFrom
{
    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        public ?string $env = null,
        public ?string $method = null,
    ) {
        if ($env === null && $method === null) {
            throw new InvalidArgumentException('DefaultFrom requires env or method.');
        }

        if ($method !== null && preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $method) !== 1) {
            throw new InvalidArgumentException('Invalid DefaultFrom method name.');
        }
    }
}
