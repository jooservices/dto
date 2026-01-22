<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use RuntimeException;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function is_dir;
use function mkdir;
use function serialize;
use function sha1;
use function unlink;
use function unserialize;

final class FileMetaCache implements MetaCacheInterface
{
    private const string FILE_EXTENSION = '.meta.cache';

    /** @var array<class-string, ClassMeta> */
    private array $memoryCache = [];

    public function __construct(
        private readonly string $cacheDirectory,
    ) {
        $this->ensureDirectoryExists();
    }

    public function get(string $className): ?ClassMeta
    {
        if (isset($this->memoryCache[$className])) {
            return $this->memoryCache[$className];
        }

        $filePath = $this->getCacheFilePath($className);

        if (! file_exists($filePath)) {
            return null;
        }

        $contents = file_get_contents($filePath);

        if ($contents === false) {
            return null;
        }

        $meta = @unserialize($contents);

        if (! $meta instanceof ClassMeta) {
            return null;
        }

        $this->memoryCache[$className] = $meta;

        return $meta;
    }

    public function set(string $className, ClassMeta $meta): void
    {
        $this->memoryCache[$className] = $meta;

        $filePath = $this->getCacheFilePath($className);
        $result = @file_put_contents($filePath, serialize($meta));

        if ($result === false) {
            throw new RuntimeException("Failed to write cache file: {$filePath}");
        }
    }

    public function has(string $className): bool
    {
        if (isset($this->memoryCache[$className])) {
            return true;
        }

        return file_exists($this->getCacheFilePath($className));
    }

    public function clear(): void
    {
        $this->memoryCache = [];

        $files = glob($this->cacheDirectory.'/*'.self::FILE_EXTENSION);

        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            unlink($file);
        }
    }

    /**
     * @param  class-string  $className
     */
    public function warmup(string $className, ClassMeta $meta): void
    {
        $this->set($className, $meta);
    }

    /**
     * @param  class-string  $className
     */
    private function getCacheFilePath(string $className): string
    {
        $hash = sha1($className);

        return $this->cacheDirectory.'/'.$hash.self::FILE_EXTENSION;
    }

    private function ensureDirectoryExists(): void
    {
        if (is_dir($this->cacheDirectory)) {
            return;
        }

        $result = @mkdir($this->cacheDirectory, 0o755, true);

        if (! $result && ! is_dir($this->cacheDirectory)) {
            throw new RuntimeException("Failed to create cache directory: {$this->cacheDirectory}");
        }
    }
}
