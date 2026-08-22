<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureRegexDto extends Dto
{
    public function __construct(
        #[Regex(pattern: '/^[a-z]+$/')]
        public readonly ?string $value = null,
    ) {
    }
}
