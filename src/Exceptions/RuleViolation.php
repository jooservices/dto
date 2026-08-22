<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

/**
 * Single validation rule failure.
 */
final readonly class RuleViolation
{
    public function __construct(
        public string $property,
        public string $rule,
        public string $message,
        public mixed $value = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'property' => $this->property,
            'rule' => $this->rule,
            'message' => $this->message,
            'value' => $this->value,
        ];
    }
}
