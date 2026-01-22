<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

interface CasterInterface
{
    public function supports(PropertyMeta $property, mixed $value): bool;

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed;
}
