<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\Validation\Valid;
use JOOservices\Dto\Core\Dto;

final class ValidationFixtureValidListDto extends Dto
{
    public function __construct(
        /** @var list<mixed> */
        #[Valid]
        public readonly array $items,
    ) {
    }
}
