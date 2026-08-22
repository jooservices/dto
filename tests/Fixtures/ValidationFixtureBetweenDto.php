<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Between;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureBetweenDto extends Dto
{
    public function __construct(
        #[Between(min: 1, max: 10)]
        public readonly ?string $score = null,
    ) {
    }
}
