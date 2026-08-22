<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\Redact;
use JOOservices\Dto\Core\Dto;

final class HiddenAndRedactConflictDto extends Dto
{
    public function __construct(
        #[Hidden]
        #[Redact]
        public readonly string $secret,
    ) {
    }
}
