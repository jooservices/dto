<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

/**
 * Shared numeric parsing for Min/Max/Between validators.
 */
final class NumericRuleSupport
{
    public function asComparableFloat(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_int($value) || is_float($value)) {
            return (float) $value;
        }

        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        return null;
    }
}
