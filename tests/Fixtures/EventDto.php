<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use DateTimeImmutable;
use JOOservices\Dto\Core\Dto;

final class EventDto extends Dto
{
    public function __construct(
        public readonly DateTimeImmutable $startedAt,
    ) {
    }
}
