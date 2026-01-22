<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use DateTimeImmutable;
use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Core\Dto;

final class UserDto extends Dto
{
    public function __construct(
        public readonly string $id,
        #[MapFrom('email_address')]
        public readonly string $email,
        public readonly string $name,
        public readonly DateTimeImmutable $createdAt,
        public readonly ?AddressDto $address = null,
        public readonly Status $status = Status::Pending,
        #[Hidden]
        public readonly ?string $passwordHash = null,
    ) {}
}
