<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Normalization\TransformerInterface;

/**
 * TransformWith transformer applied on the way out (serialization), not on hydration.
 */
final class AttrFixtureUpperOutputTransformer implements TransformerInterface
{
    public function supports(mixed $value): bool
    {
        return is_string($value);
    }

    public function transform(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return strtoupper($value);
    }
}
