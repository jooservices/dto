<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class UserDto extends Dto
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
        public readonly ?string $email = null,
    ) {
    }
}
