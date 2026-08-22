<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureRequiredDto extends Dto
{
    public function __construct(
        #[Required]
        public readonly ?string $value = null,
    ) {
    }
}
