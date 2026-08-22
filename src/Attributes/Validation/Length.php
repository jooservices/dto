<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Length implements ValidationRuleAttribute
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
        public ?string $message = null,
    ) {
    }
}
