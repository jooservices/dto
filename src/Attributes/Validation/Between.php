<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a numeric value is between a minimum and maximum value (inclusive).
 *
 * Usage:
 *   #[Between(1, 100)]
 *   public readonly int $percentage;
 *
 *   #[Between(0.01, 999.99, message: 'Price must be between 0.01 and 999.99')]
 *   public readonly float $price;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Between
{
    public function __construct(
        public int|float $min,
        public int|float $max,
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? "The value must be between {$this->min} and {$this->max}";
    }
}
