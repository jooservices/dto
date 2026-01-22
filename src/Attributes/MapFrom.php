<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class MapFrom
{
    public function __construct(
        public string $key,
    ) {}
}
