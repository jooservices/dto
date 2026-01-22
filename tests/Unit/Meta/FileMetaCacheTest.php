<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\FileMetaCache;
use JOOservices\Dto\Tests\TestCase;
use RuntimeException;

final class FileMetaCacheTest extends TestCase
{
    private string $tempDir;

    private FileMetaCache $cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempDir = sys_get_temp_dir().'/dto_test_cache_'.uniqid();
        $this->cache = new FileMetaCache($this->tempDir);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $files = glob($this->tempDir.'/*');

            if ($files !== false) {
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
            }
            rmdir($this->tempDir);
        }

        parent::tearDown();
    }

    public function test_constructor_creates_directory(): void
    {
        $this->assertDirectoryExists($this->tempDir);
    }

    public function test_constructor_throws_exception_when_cannot_create_directory(): void
    {
        $invalidDir = '/root/cannot_create_'.uniqid();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to create cache directory');

        new FileMetaCache($invalidDir);
    }

    public function test_get_returns_null_for_non_existent_class(): void
    {
        $result = $this->cache->get('NonExistent\\Class');

        $this->assertNull($result);
    }

    public function test_set_and_get(): void
    {
        $className = $this->faker->word().'\\TestClass';
        $meta = $this->createClassMeta($className);

        $this->cache->set($className, $meta);
        $result = $this->cache->get($className);

        $this->assertInstanceOf(ClassMeta::class, $result);
        $this->assertSame($className, $result->className);
    }

    public function test_has_returns_false_for_non_existent_class(): void
    {
        $this->assertFalse($this->cache->has('NonExistent\\Class'));
    }

    public function test_has_returns_true_after_set(): void
    {
        $className = $this->faker->word().'\\TestClass';
        $meta = $this->createClassMeta($className);

        $this->cache->set($className, $meta);

        $this->assertTrue($this->cache->has($className));
    }

    public function test_has_checks_memory_cache_first(): void
    {
        $className = $this->faker->word().'\\TestClass';
        $meta = $this->createClassMeta($className);

        $this->cache->set($className, $meta);

        $cacheFilePath = $this->getCacheFilePath($className);
        unlink($cacheFilePath);

        $this->assertTrue($this->cache->has($className));
    }

    public function test_get_uses_memory_cache_first(): void
    {
        $className = $this->faker->word().'\\TestClass';
        $meta = $this->createClassMeta($className);

        $this->cache->set($className, $meta);

        $cacheFilePath = $this->getCacheFilePath($className);
        unlink($cacheFilePath);

        $result = $this->cache->get($className);

        $this->assertInstanceOf(ClassMeta::class, $result);
        $this->assertSame($className, $result->className);
    }

    public function test_clear(): void
    {
        $className1 = 'Test\\Class1';
        $className2 = 'Test\\Class2';

        $this->cache->set($className1, $this->createClassMeta($className1));
        $this->cache->set($className2, $this->createClassMeta($className2));

        $this->assertTrue($this->cache->has($className1));
        $this->assertTrue($this->cache->has($className2));

        $this->cache->clear();

        $this->assertFalse($this->cache->has($className1));
        $this->assertFalse($this->cache->has($className2));
    }

    public function test_clear_removes_files(): void
    {
        $className = 'Test\\Class';
        $this->cache->set($className, $this->createClassMeta($className));

        $files = glob($this->tempDir.'/*.meta.cache');
        $this->assertNotEmpty($files);

        $this->cache->clear();

        $files = glob($this->tempDir.'/*.meta.cache');
        $this->assertEmpty($files);
    }

    public function test_warmup(): void
    {
        $className = $this->faker->word().'\\TestClass';
        $meta = $this->createClassMeta($className);

        $this->cache->warmup($className, $meta);

        $this->assertTrue($this->cache->has($className));
        $result = $this->cache->get($className);
        $this->assertSame($className, $result->className);
    }

    public function test_get_returns_null_for_corrupted_file(): void
    {
        $className = 'Test\\Class';
        $cacheFilePath = $this->getCacheFilePath($className);

        if (! is_dir($this->tempDir)) {
            mkdir($this->tempDir, 0o755, true);
        }

        file_put_contents($cacheFilePath, 'corrupted data');

        $result = $this->cache->get($className);

        $this->assertNull($result);
    }

    public function test_set_throws_exception_on_write_failure(): void
    {
        $cache = new FileMetaCache($this->tempDir);
        $className = 'Test\\Class';
        $meta = $this->createClassMeta($className);

        chmod($this->tempDir, 0o444);

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Failed to write cache file');

            $cache->set($className, $meta);
        } finally {
            chmod($this->tempDir, 0o755);
        }
    }

    public function test_clear_handles_non_existent_files(): void
    {
        $cache = new FileMetaCache($this->tempDir);

        $cache->clear();

        $this->assertDirectoryExists($this->tempDir);
    }

    private function createClassMeta(string $className): ClassMeta
    {
        return new ClassMeta(
            className: $className,
            isReadonly: false,
            properties: [],
            constructorParams: [],
            attributes: [],
        );
    }

    private function getCacheFilePath(string $className): string
    {
        $hash = sha1($className);

        return $this->tempDir.'/'.$hash.'.meta.cache';
    }
}
