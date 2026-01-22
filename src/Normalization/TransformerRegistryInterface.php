<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

interface TransformerRegistryInterface
{
    public function register(TransformerInterface $transformer, int $priority = 0): void;

    public function get(PropertyMeta $property, mixed $value): ?TransformerInterface;

    public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed;

    public function canTransform(PropertyMeta $property, mixed $value): bool;
}
