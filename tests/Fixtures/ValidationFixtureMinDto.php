<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Min;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureMinDto extends Dto
{
    public function __construct(
        #[Min(min: 10)]
        public readonly ?string $value = null,
    ) {
    }
}
