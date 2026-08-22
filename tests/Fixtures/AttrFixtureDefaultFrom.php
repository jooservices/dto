<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Core\Dto;

final class AttrFixtureDefaultFrom extends Dto
{
    public function __construct(
        #[DefaultFrom(method: 'defaultRegion')]
        public readonly string $region,
        #[DefaultFrom(env: 'ATTR_FIXTURE_DEFAULT_FROM_ZONE')]
        public readonly ?string $zone = null,
    ) {
    }

    public static function defaultRegion(): string
    {
        return 'us-east-1';
    }
}
