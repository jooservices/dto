<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Length;
use JOOservices\Dto\Core\Dto;

final class NameDto extends Dto
{
    public function __construct(
        #[Length(min: 1, max: 5)]
        public readonly string $name,
    ) {
    }
}
