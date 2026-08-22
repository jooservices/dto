<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

final class AttrFixtureSquare extends AttrFixtureShape
{
    public function __construct(
        public readonly float $side,
    ) {
        parent::__construct('square');
    }
}
