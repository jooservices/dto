<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Core\Dto;

/**
 * Base class for a DiscriminatorMap hierarchy. The "base" and "invalid" map entries exist
 * purely to exercise the same-class and not-a-subclass branches of DiscriminatorResolver.
 */
#[DiscriminatorMap(field: 'type', map: [
    'circle' => AttrFixtureCircle::class,
    'square' => AttrFixtureSquare::class,
    'base' => AttrFixtureShape::class,
    'invalid' => UserDto::class,
])]
class AttrFixtureShape extends Dto
{
    public function __construct(
        public readonly string $type,
    ) {
    }
}
