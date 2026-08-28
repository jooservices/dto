<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureVarWinsOverParamDto extends Dto
{
    /**
     * @param  array<string, string>  $values
     */
    public function __construct(
        /** @var array<int> */
        public readonly array $values,
    ) {
    }
}
