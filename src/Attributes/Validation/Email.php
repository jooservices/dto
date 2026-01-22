<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a value is a valid email address.
 *
 * Usage:
 *   #[Email]
 *   public readonly string $email;
 *
 *   #[Email(message: 'Please provide a valid email')]
 *   public readonly string $contactEmail;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Email
{
    public function __construct(
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? 'The value must be a valid email address';
    }
}
