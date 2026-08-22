<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\ClassMeta;

interface MapperInterface
{
    /**
     * @param  array<string, mixed>  $source
     * @return array<string, mixed>
     */
    public function map(array $source, ClassMeta $meta, ?Context $ctx): array;
}
