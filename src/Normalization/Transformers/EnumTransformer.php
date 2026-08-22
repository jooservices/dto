<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization\Transformers;

use BackedEnum;
use JOOservices\Dto\Normalization\TransformerInterface;
use UnitEnum;

final class EnumTransformer implements TransformerInterface
{
    public function supports(mixed $value): bool
    {
        return $value instanceof UnitEnum;
    }

    public function transform(mixed $value): mixed
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return $value;
    }
}
