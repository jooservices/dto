<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Validates that a string's length is within specified bounds.
 *
 * Usage:
 *   #[Length(min: 3, max: 50)]
 *   public readonly string $username;
 *
 *   #[Length(min: 8, message: 'Password must be at least 8 characters')]
 *   public readonly string $password;
 *
 *   #[Length(max: 255)]
 *   public readonly string $description;
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Length
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
        public ?string $message = null,
    ) {}

    public function getMessage(): string
    {
        if ($this->message !== null) {
            return $this->message;
        }

        if ($this->min !== null && $this->max !== null) {
            return "The length must be between {$this->min} and {$this->max} characters";
        }

        if ($this->min !== null) {
            return "The length must be at least {$this->min} characters";
        }

        if ($this->max !== null) {
            return "The length must be at most {$this->max} characters";
        }

        return 'Invalid length';
    }
}
