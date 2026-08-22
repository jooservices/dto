<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Meta\FileMetaCache;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRegexDto;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use PHPUnit\Framework\Assert;
use ReflectionException;
use RuntimeException;

final class FileMetaCacheTest extends TestCase
{
    private string $directory;

    private string $signingKey = 'unit-test-signing-key';

    protected function setUp(): void
    {
        parent::setUp();
        $this->directory = sys_get_temp_dir() . '/dto-file-meta-cache-' . uniqid('', true);
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        $files = glob($this->directory . '/*');
        if ($files !== false) {
            foreach ($files as $file) {
                @unlink($file);
            }
        }
        @rmdir($this->directory);
        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException
     * @throws \JOOservices\Dto\Exceptions\HydrationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \Random\RandomException
     */
    public function testRoundTripPreservesValidationAttributeInstances(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(ValidationFixtureRegexDto::class);

        $cache = new FileMetaCache($this->directory, $this->signingKey);
        $cache->set(ValidationFixtureRegexDto::class, $meta);

        $loaded = $cache->get(ValidationFixtureRegexDto::class);
        self::assertNotNull($loaded);

        $property = $loaded->property('value');
        self::assertNotNull($property);

        $rule = $property->resolved->validationRules[0] ?? null;
        self::assertInstanceOf(ValidationRuleAttribute::class, $rule);
        self::assertInstanceOf(Regex::class, $rule);
    }

    /**
     * @throws InvalidArgumentException
     * @throws \JOOservices\Dto\Exceptions\HydrationException
     * @throws JsonException
     * @throws ReflectionException
     * @throws RuntimeException
     * @throws \Random\RandomException
     */
    public function testTamperedPayloadIsRejected(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(ValidationFixtureRegexDto::class);

        $cache = new FileMetaCache($this->directory, $this->signingKey);
        $cache->set(ValidationFixtureRegexDto::class, $meta);

        $path = $this->directory . '/' . hash(
            'xxh3',
            $this->signingKey . "\0" . ValidationFixtureRegexDto::class,
        ) . '.cache';
        file_put_contents(
            $path,
            '{"v":"2","class":"' . ValidationFixtureRegexDto::class . '","hash":"deadbeef","payload":"YQ=="}',
        );

        $fresh = new FileMetaCache($this->directory, $this->signingKey);
        Assert::assertNull($fresh->get(ValidationFixtureRegexDto::class));
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testRejectsEmptySigningKey(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new FileMetaCache($this->directory, '');
    }
}
