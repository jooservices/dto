<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Redact;
use JOOservices\Dto\Core\Dto;

final class SecretDto extends Dto
{
    public function __construct(
        public readonly string $username,
        #[Redact]
        public readonly string $password = '',
        #[Redact(with: '****')]
        public readonly ?string $apiToken = null,
    ) {
    }
}
