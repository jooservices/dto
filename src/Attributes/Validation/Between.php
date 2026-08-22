<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Between implements ValidationRuleAttribute
{
    public function __construct(
        public float | int $min,
        public float | int $max,
        public ?string $message = null,
    ) {
    }
}
