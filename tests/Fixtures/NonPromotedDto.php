<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class NonPromotedDto extends Dto
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function exposedName(): string
    {
        return $this->name;
    }
}
