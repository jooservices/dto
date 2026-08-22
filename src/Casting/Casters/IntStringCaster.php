<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;

final class IntStringCaster
{
    public function __construct(
        private readonly ScalarCastSupport $support = new ScalarCastSupport(),
    ) {
    }

    /**
     * @throws CastException
     */
    public function cast(PropertyMeta $property, string $value, string $mode): int
    {
        if (! is_numeric($value)) {
            throw $this->support->mismatch($property, 'int', $value);
        }

        if (str_contains($value, '.') || str_contains(strtolower($value), 'e')) {
            $asFloat = (float) $value;
            $this->support->rejectNonFinite($property, $asFloat, 'int');

            return (int) $asFloat;
        }

        if (preg_match('/^-?\d+$/', $value) !== 1 && $mode === CastMode::Strict->value) {
            throw $this->support->mismatch($property, 'int', $value);
        }

        $asInt = (int) $value;
        if ((string) $asInt !== ltrim($value, '+')) {
            $this->assertWithinIntegerRange($property, $value);
        }

        return $asInt;
    }

    /**
     * @throws CastException
     */
    private function assertWithinIntegerRange(PropertyMeta $property, string $value): void
    {
        $digits = ltrim($value, '+-');
        if (strlen($digits) >= strlen((string) PHP_INT_MAX) && $digits > (string) PHP_INT_MAX) {
            throw $this->support->mismatch($property, 'int', $value);
        }
    }
}
