<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Core\Dto;

final class CoreFixtureDefaultFromPrivateMethodDto extends Dto
{
    public function __construct(
        #[DefaultFrom(method: 'privateFallback')]
        public readonly int $value,
    ) {
    }

    /**
     * Referenced by {@see DefaultFrom} via method name.
     *
     * @phpstan-ignore method.unused
     */
    private static function privateFallback(): int
    {
        return 99;
    }
}
