<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\ClassMeta;

interface HydratorInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function hydrate(ClassMeta $meta, array $data, ?Context $ctx): object;
}
