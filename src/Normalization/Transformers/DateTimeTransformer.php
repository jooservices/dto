<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization\Transformers;

use DateTimeInterface;
use JOOservices\Dto\Normalization\TransformerInterface;

final class DateTimeTransformer implements TransformerInterface
{
    public function supports(mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function transform(mixed $value): mixed
    {
        if (! $value instanceof DateTimeInterface) {
            return $value;
        }

        return $value->format(DateTimeInterface::ATOM);
    }
}
