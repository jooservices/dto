<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class IntersectionHolderDto extends Dto implements CountableLabel, CountableValue
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    public function label(): string
    {
        return $this->name;
    }

    public function count(): int
    {
        return strlen($this->name);
    }
}
