<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

final class AttrFixtureCircle extends AttrFixtureShape
{
    public function __construct(
        public readonly float $radius,
    ) {
        parent::__construct('circle');
    }
}
