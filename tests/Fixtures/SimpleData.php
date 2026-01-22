<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Data;

final class SimpleData extends Data
{
    public function __construct(
        public string $name,
        public int $age,
        public ?string $email = null,
    ) {}
}
