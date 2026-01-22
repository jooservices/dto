<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

/**
 * Mark a property for nested validation.
 *
 * When applied to a nested DTO or array of DTOs, triggers validation
 * of the nested objects during the parent's validation.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Valid
{
    public function __construct(
        public bool $eachItem = false,
    ) {}
}
