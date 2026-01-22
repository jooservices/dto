<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

interface CasterRegistryInterface
{
    public function register(CasterInterface $caster, int $priority = 0): void;

    public function get(PropertyMeta $property, mixed $value): ?CasterInterface;

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed;

    public function canCast(PropertyMeta $property, mixed $value): bool;
}
