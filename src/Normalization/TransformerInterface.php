<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

interface TransformerInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool;

    public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed;
}
