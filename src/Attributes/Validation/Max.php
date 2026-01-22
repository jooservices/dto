<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a numeric value is at most a maximum value.
 *
 * Usage:
 *   #[Max(120)]
 *   public readonly int $age;
 *
 *   #[Max(999.99, message: 'Price cannot exceed 999.99')]
 *   public readonly float $price;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Max
{
    public function __construct(
        public float|int $max,
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        return $this->message ?? "The value must be at most {$this->max}";
    }
}
