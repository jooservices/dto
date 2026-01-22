<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

interface MetaFactoryInterface
{
    /**
     * @param  class-string  $className
     */
    public function create(string $className): ClassMeta;
}
