<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Redact;
use JOOservices\Dto\Attributes\Validation\Length;
use JOOservices\Dto\Core\Dto;

final class SecretValidatedDto extends Dto
{
    public function __construct(
        #[Redact]
        #[Length(min: 8)]
        public readonly string $password,
    ) {
    }
}
