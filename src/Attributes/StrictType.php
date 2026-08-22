<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class StrictType
{
    public function __construct(
        public string $message = 'Type mismatch',
    ) {
    }
}
