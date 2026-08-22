<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

/**
 * Deliberately NOT a subclass of AbstractDto. Used to prove discriminator/nested
 * hydration targets are rejected when they do not resolve to a real DTO.
 */
final class CoreFixtureNotADto
{
    public function __construct(
        public readonly string $kind = 'n/a',
    ) {
    }
}
