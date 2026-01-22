<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use WeakMap;

/**
 * Thread-safe cache for computed properties that works with readonly DTOs.
 * Uses WeakMap to avoid memory leaks - entries auto-removed when DTO is GC'd.
 */
final class ComputedCache
{
    /** @var WeakMap<object, array<string, mixed>>|null */
    private static ?WeakMap $cache = null;

    public static function get(object $dto, string $propertyName): mixed
    {
        self::ensureInitialized();

        $cache = self::$cache;
        if ($cache === null || ! isset($cache[$dto])) {
            return null;
        }

        return $cache[$dto][$propertyName] ?? null;
    }

    public static function set(object $dto, string $propertyName, mixed $value): void
    {
        self::ensureInitialized();

        $cache = self::$cache;
        if ($cache === null) {
            return;
        }

        if (! isset($cache[$dto])) {
            $cache[$dto] = [];
        }

        $cache[$dto][$propertyName] = $value;
    }

    public static function has(object $dto, string $propertyName): bool
    {
        self::ensureInitialized();

        $cache = self::$cache;
        if ($cache === null || ! isset($cache[$dto])) {
            return false;
        }

        return array_key_exists($propertyName, $cache[$dto]);
    }

    /**
     * Clear cache for a specific DTO instance.
     */
    public static function clear(object $dto): void
    {
        self::ensureInitialized();

        $cache = self::$cache;
        if ($cache !== null && isset($cache[$dto])) {
            unset($cache[$dto]);
        }
    }

    /**
     * Clear all cached data (primarily for testing).
     */
    public static function clearAll(): void
    {
        self::$cache = null;
    }

    private static function ensureInitialized(): void
    {
        if (! isset(self::$cache)) {
            /** @var WeakMap<object, array<string, mixed>> $weakMap */
            $weakMap = new WeakMap;
            self::$cache = $weakMap;
        }
    }
}
