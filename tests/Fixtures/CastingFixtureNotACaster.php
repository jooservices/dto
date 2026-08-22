<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

/**
 * Deliberately does NOT implement CasterInterface, used to exercise the
 * CastWithExecutor guard that rejects misconfigured #[CastWith] targets.
 */
final class CastingFixtureNotACaster
{
}
