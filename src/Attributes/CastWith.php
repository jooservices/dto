<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class CastWith
{
    /**
     * @param  class-string  $casterClass
     * @param  array<int|string, mixed>  $options  Constructor-spread arguments
     */
    public function __construct(
        public string $casterClass,
        public array $options = [],
    ) {
    }
}
