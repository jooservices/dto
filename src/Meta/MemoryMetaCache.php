<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

/**
 * In-memory ClassMeta cache with true LRU eviction (bump on get).
 */
final class MemoryMetaCache implements MetaCacheInterface
{
    /** @var array<string, ClassMeta> */
    private array $entries = [];

    /**
     * @param  int  $maxEntries  0 = unlimited
     */
    public function __construct(
        private readonly int $maxEntries = 0,
    ) {
    }

    public function get(string $className): ?ClassMeta
    {
        if (! array_key_exists($className, $this->entries)) {
            return null;
        }

        $meta = $this->entries[$className];
        if ($this->maxEntries > 0) {
            unset($this->entries[$className]);
            $this->entries[$className] = $meta;
        }

        return $meta;
    }

    public function set(string $className, ClassMeta $meta): void
    {
        if (array_key_exists($className, $this->entries)) {
            unset($this->entries[$className]);
        }

        $this->entries[$className] = $meta;
        $this->evictIfNeeded();
    }

    public function has(string $className): bool
    {
        return array_key_exists($className, $this->entries);
    }

    public function clear(?string $className = null): void
    {
        if ($className === null) {
            $this->entries = [];

            return;
        }

        unset($this->entries[$className]);
    }

    public function getCount(): int
    {
        return count($this->entries);
    }

    /**
     * @return list<string>
     */
    public function getCachedClasses(): array
    {
        return array_keys($this->entries);
    }

    private function evictIfNeeded(): void
    {
        if ($this->maxEntries <= 0) {
            return;
        }

        while ($this->getCount() > $this->maxEntries) {
            $oldest = array_key_first($this->entries);
            if ($oldest === null) {
                return;
            }
            unset($this->entries[$oldest]);
        }
    }
}
