<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization\Transformers;

use BackedEnum;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Normalization\TransformerInterface;
use UnitEnum;

final class EnumTransformer implements TransformerInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $value instanceof UnitEnum;
    }

    public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): int|string
    {
        if ($value instanceof BackedEnum) {
            return $value->value;
        }

        if ($value instanceof UnitEnum) {
            return $value->name;
        }

        return '';
    }
}
