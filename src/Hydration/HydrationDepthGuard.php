<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;

final class HydrationDepthGuard
{
    /**
     * @throws HydrationException
     */
    public function assertWithinLimit(Context $ctx): void
    {
        $depth = is_int($ctx->customData['hydrationDepth'] ?? null)
            ? $ctx->customData['hydrationDepth']
            : 0;
        $max = is_int($ctx->customData['maxHydrationDepth'] ?? null)
            ? $ctx->customData['maxHydrationDepth']
            : 32;
        if ($depth <= $max) {
            return;
        }

        throw new HydrationException(
            message: 'Hydration depth exceeded.',
            givenValue: $depth,
        );
    }
}
