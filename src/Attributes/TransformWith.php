<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class TransformWith
{
    /**
     * @param  class-string  $transformerClass
     * @param  array<string, mixed>  $options
     */
    public function __construct(
        public string $transformerClass,
        public array $options = [],
    ) {}
}
