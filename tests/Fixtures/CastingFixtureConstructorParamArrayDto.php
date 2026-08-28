<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureConstructorParamArrayDto extends Dto
{
    /**
     * @param  list<int>  $ids
     * @param  array<string, string>  $labels
     */
    public function __construct(
        public readonly array $ids = [],
        public readonly array $labels = [],
    ) {
    }
}
