<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes\Validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Url implements ValidationRuleAttribute
{
    /**
     * @param  list<string>  $schemes
     */
    public function __construct(
        public ?string $message = null,
        public array $schemes = ['http', 'https'],
    ) {
    }
}
