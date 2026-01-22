<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Marks a property as required.
 *
 * When validation is enabled, this attribute ensures the property
 * has a non-null, non-empty value.
 *
 * Usage:
 *   #[Required]
 *   public readonly string $name;
 *
 *   #[Required(message: 'Email is mandatory')]
 *   public readonly string $email;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Required
{
    public function __construct(
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? 'This field is required';
    }
}
