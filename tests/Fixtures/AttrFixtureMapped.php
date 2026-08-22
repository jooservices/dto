<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Attributes\MapTo;
use JOOservices\Dto\Core\Dto;

/**
 * Exercises MapFrom (input key aliasing) and MapTo (output key aliasing) together.
 */
final class AttrFixtureMapped extends Dto
{
    public function __construct(
        #[MapFrom('full_name')]
        #[MapTo('displayName')]
        public readonly string $name,
        public readonly int $age,
    ) {
    }
}
