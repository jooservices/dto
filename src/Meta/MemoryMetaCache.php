<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

final class MemoryMetaCache implements MetaCacheInterface
{
    /** @var array<class-string, ClassMeta> */
    private array $cache = [];

    public function get(string $className): ?ClassMeta
    {
        return $this->cache[$className] ?? null;
    }

    public function set(string $className, ClassMeta $meta): void
    {
        $this->cache[$className] = $meta;
    }

    public function has(string $className): bool
    {
        return isset($this->cache[$className]);
    }

    public function clear(): void
    {
        $this->cache = [];
    }

    public function getCount(): int
    {
        return count($this->cache);
    }

    /**
     * @return array<class-string>
     */
    public function getCachedClasses(): array
    {
        return array_keys($this->cache);
    }
}
