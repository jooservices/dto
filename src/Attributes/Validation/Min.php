<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a numeric value is at least a minimum value.
 *
 * Usage:
 *   #[Min(18)]
 *   public readonly int $age;
 *
 *   #[Min(0.01, message: 'Price must be positive')]
 *   public readonly float $price;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Min
{
    public function __construct(
        public int|float $min,
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? "The value must be at least {$this->min}";
    }
}
