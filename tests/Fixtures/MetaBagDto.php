<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class MetaBagDto extends Dto
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public readonly array $meta,
    ) {
    }
}
