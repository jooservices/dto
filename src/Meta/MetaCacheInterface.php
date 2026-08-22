<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

interface MetaCacheInterface
{
    public function get(string $className): ?ClassMeta;

    public function set(string $className, ClassMeta $meta): void;

    public function has(string $className): bool;

    /**
     * @param  string|null  $className  null clears the entire cache
     */
    public function clear(?string $className = null): void;
}
