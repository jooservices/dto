<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Core\Dto;

final class CoreFixtureDefaultFromDto extends Dto
{
    public function __construct(
        #[DefaultFrom(env: 'COREFIXTURE_DEFAULT_AGE', method: 'defaultAge')]
        public readonly int $age,
    ) {
    }

    public static function defaultAge(): int
    {
        return 42;
    }
}
