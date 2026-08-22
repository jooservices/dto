<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureEmailDto extends Dto
{
    public function __construct(
        #[Email]
        public readonly ?string $email = null,
    ) {
    }
}
