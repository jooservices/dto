<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class RequiredIf implements ValidationRuleAttribute
{
    public function __construct(
        public string $field,
        public mixed $value,
        public ?string $message = null,
    ) {
    }
}
