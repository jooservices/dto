<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a property is required if another field has a specific value.
 *
 * Usage:
 *   #[RequiredIf('subscribeNewsletter', true)]
 *   public readonly ?string $email;
 *
 *   #[RequiredIf('paymentMethod', 'credit_card', message: 'Card number is required for credit card payments')]
 *   public readonly ?string $cardNumber;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RequiredIf
{
    public function __construct(
        public string $field,
        public mixed $value,
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? "This field is required when {$this->field} is set";
    }
}
