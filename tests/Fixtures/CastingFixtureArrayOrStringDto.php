<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureArrayOrStringDto extends Dto
{
    /**
     * @param  array<int|string, mixed>|string  $value
     */
    public function __construct(
        public readonly array | string $value,
    ) {
    }
}
