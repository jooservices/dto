<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class ProfileDto extends Dto
{
    public function __construct(
        public readonly UserDto $user,
        public readonly AddressDto $address,
    ) {
    }
}
