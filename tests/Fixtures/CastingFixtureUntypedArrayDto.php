<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureUntypedArrayDto extends Dto
{
    /**
     * @param  array<int|string, mixed>  $categories
     */
    public function __construct(
        public readonly array $categories = [],
    ) {
    }
}
