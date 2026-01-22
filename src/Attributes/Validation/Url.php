<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a value is a valid URL.
 *
 * Usage:
 *   #[Url]
 *   public readonly string $website;
 *
 *   #[Url(message: 'Please provide a valid URL')]
 *   public readonly string $homepage;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Url
{
    public function __construct(
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? 'The value must be a valid URL';
    }
}
