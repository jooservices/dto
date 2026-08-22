<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;

final class ScalarCastSupport
{
    /**
     * @throws CastException
     */
    public function rejectNonFinite(PropertyMeta $property, float $value, string $expected): void
    {
        if (is_finite($value)) {
            return;
        }

        throw new CastException(
            message: 'INF and NaN are not allowed.',
            path: $property->name,
            expectedType: $expected,
            givenType: get_debug_type($value),
            givenValue: $property->redactWith ?? $value,
        );
    }

    public function mismatch(PropertyMeta $property, string $expected, mixed $value): CastException
    {
        return new CastException(
            message: 'Unable to cast value.',
            path: $property->name,
            expectedType: $expected,
            givenType: get_debug_type($value),
            givenValue: $property->redactWith ?? $value,
        );
    }

    /**
     * @throws CastException
     */
    public function rejectScalarCoercion(
        PropertyMeta $property,
        string $expected,
        mixed $value,
        ?Context $ctx,
    ): void {
        if ($ctx !== null && $ctx->shouldDisallowScalarCoercion()) {
            throw $this->mismatch($property, $expected, $value);
        }
    }
}
