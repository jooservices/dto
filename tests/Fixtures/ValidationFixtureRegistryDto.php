<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureRegistryDto extends Dto
{
    public function __construct(
        #[Email]
        #[Regex(pattern: '/^\d+$/')]
        public readonly ?string $value = null,
    ) {
    }
}
