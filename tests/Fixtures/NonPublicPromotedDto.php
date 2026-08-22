<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class NonPublicPromotedDto extends Dto
{
    public function __construct(
        private readonly string $name,
    ) {
    }

    public function exposedName(): string
    {
        return $this->name;
    }
}
