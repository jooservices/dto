<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

interface MetaCacheInterface
{
    /**
     * @param  class-string  $className
     */
    public function get(string $className): ?ClassMeta;

    /**
     * @param  class-string  $className
     */
    public function set(string $className, ClassMeta $meta): void;

    /**
     * @param  class-string  $className
     */
    public function has(string $className): bool;

    public function clear(): void;
}
