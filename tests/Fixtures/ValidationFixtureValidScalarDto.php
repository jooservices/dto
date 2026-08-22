<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Valid;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureValidScalarDto extends Dto
{
    public function __construct(
        #[Valid]
        public readonly ?string $note = null,
    ) {
    }
}
