<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Normalization\TransformerInterface;

final class CoreFixtureUppercaseTransformer implements TransformerInterface
{
    public function __construct(
        private readonly string $prefix = '',
    ) {
    }

    public function supports(mixed $value): bool
    {
        return is_string($value);
    }

    public function transform(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->prefix . strtoupper($value);
    }
}
