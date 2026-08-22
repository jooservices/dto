<?php

declare(strict_types=1);

namespace JOOservices\Dto\Support;

use InvalidArgumentException;
use ReflectionClass;

/**
 * Instantiate a class with constructor-spread options using a bounded prototype cache.
 *
 * Prototypes are cached and cloned on each {@see make()} when cloneable so callers
 * never share mutable instance state. Non-cloneable classes receive a fresh instance
 * on every call and are not cached.
 */
final class CachedOptionedInstantiator
{
    private const int DEFAULT_MAX_ENTRIES = 256;

    /** @var array<string, object> */
    private static array $instances = [];

    private static int $maxEntries = self::DEFAULT_MAX_ENTRIES;

    /**
     * @param  class-string  $className
     * @param  array<int|string, mixed>  $options
     *
     * @throws InvalidArgumentException
     */
    public function make(string $className, array $options = []): object
    {
        if (! class_exists($className)) {
            throw new InvalidArgumentException('Class does not exist: ' . $className);
        }

        $key = $className . "\0" . serialize($options);
        if (isset(self::$instances[$key])) {
            $prototype = self::$instances[$key];
            unset(self::$instances[$key]);
            self::$instances[$key] = $prototype;

            return $this->copyInstance($className, $options, $prototype);
        }

        $prototype = $options === [] ? new $className() : new $className(...$options);
        if ($this->isCloneable($prototype)) {
            self::$instances[$key] = $prototype;
            $this->evictIfNeeded();

            return clone $prototype;
        }

        return $prototype;
    }

    /**
     * @param  class-string  $className
     * @param  array<int|string, mixed>  $options
     */
    private function copyInstance(string $className, array $options, object $prototype): object
    {
        if ($this->isCloneable($prototype)) {
            return clone $prototype;
        }

        return $options === [] ? new $className() : new $className(...$options);
    }

    private function isCloneable(object $instance): bool
    {
        return (new ReflectionClass($instance))->isCloneable();
    }

    public static function reset(): void
    {
        self::$instances = [];
        self::$maxEntries = self::DEFAULT_MAX_ENTRIES;
    }

    public static function configureMaxEntries(int $maxEntries): void
    {
        self::$maxEntries = max(0, $maxEntries);
        (new self())->evictIfNeeded();
    }

    public static function getMaxEntries(): int
    {
        return self::$maxEntries;
    }

    public static function getCachedCount(): int
    {
        return count(self::$instances);
    }

    public function clearCache(): void
    {
        self::reset();
    }

    private function evictIfNeeded(): void
    {
        if (self::$maxEntries <= 0) {
            return;
        }

        while (self::getCachedCount() > self::$maxEntries) {
            $oldest = array_key_first(self::$instances);
            if ($oldest === null) {
                return;
            }

            unset(self::$instances[$oldest]);
        }
    }
}
