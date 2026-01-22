<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\ClassMeta;

interface NormalizerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function normalize(object $instance, ClassMeta $meta, ?Context $ctx): array;
}
