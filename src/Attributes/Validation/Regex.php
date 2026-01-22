<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a value matches a regular expression pattern.
 *
 * Usage:
 *   #[Regex('/^[A-Z]{2}-\d{4}$/')]
 *   public readonly string $code;
 *
 *   #[Regex('/^\+?[1-9]\d{1,14}$/', message: 'Invalid phone number format')]
 *   public readonly string $phone;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Regex
{
    public function __construct(
        public string $pattern,
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? 'The value does not match the required pattern';
    }
}
