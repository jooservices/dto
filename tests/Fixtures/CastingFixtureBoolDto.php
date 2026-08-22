<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureBoolDto extends Dto
{
    public function __construct(
        public readonly bool $flag,
        public readonly ?bool $optionalFlag = null,
    ) {
    }
}
