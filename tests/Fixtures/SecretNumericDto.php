<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Redact;
use JOOservices\Dto\Core\Dto;

final class SecretNumericDto extends Dto
{
    public function __construct(
        #[Redact]
        public readonly int $pin,
    ) {
    }
}
