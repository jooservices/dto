<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Core\Dto;

final class CoreFixtureMapFromDto extends Dto
{
    public function __construct(
        #[MapFrom('full_name')]
        public readonly string $name,
        public readonly int $age = 0,
    ) {
    }
}
