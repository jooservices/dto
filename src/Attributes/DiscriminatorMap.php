<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Class-level polymorphism. The discriminator field is read after naming and MapFrom.
 *
 * @phpstan-type ClassMap array<string, class-string>
 */
#[Attribute(Attribute::TARGET_CLASS)]
final readonly class DiscriminatorMap
{
    /**
     * @param  array<string, class-string>  $map
     */
    public function __construct(
        public string $field,
        public array $map,
    ) {
    }
}
