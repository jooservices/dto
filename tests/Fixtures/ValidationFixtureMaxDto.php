<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Max;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureMaxDto extends Dto
{
    public function __construct(
        #[Max(max: 100)]
        public readonly ?string $value = null,
    ) {
    }
}
