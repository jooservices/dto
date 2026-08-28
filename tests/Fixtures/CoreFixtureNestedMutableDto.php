<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Core\Dto;

final class CoreFixtureNestedMutableDto extends Dto
{
    public function __construct(
        public readonly UserData $profile,
        #[Hidden]
        public readonly string $token,
    ) {
    }
}
