<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

interface CasterRegistryInterface
{
    public function register(CasterInterface $caster): void;

    public function firstMatching(
        TypeDescriptor $type,
        PropertyMeta $property,
        mixed $value,
        ?Context $ctx,
    ): ?CasterInterface;
}
