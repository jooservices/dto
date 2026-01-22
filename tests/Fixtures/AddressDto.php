<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class AddressDto extends Dto
{
    public function __construct(
        public readonly string $street,
        public readonly string $city,
        public readonly string $country,
        public readonly ?string $postalCode = null,
    ) {}
}
