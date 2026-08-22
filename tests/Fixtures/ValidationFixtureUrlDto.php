<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Url;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureUrlDto extends Dto
{
    public function __construct(
        #[Url]
        public readonly ?string $website = null,
    ) {
    }
}
