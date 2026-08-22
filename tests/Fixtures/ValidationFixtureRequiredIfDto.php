<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\RequiredIf;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureRequiredIfDto extends Dto
{
    public function __construct(
        public readonly bool $active = false,
        #[RequiredIf(field: 'active', value: true)]
        public readonly ?string $reason = null,
    ) {
    }
}
