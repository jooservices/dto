<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use InvalidArgumentException;
use JsonException;
use RuntimeException;

/**
 * File-backed ClassMeta cache with HMAC envelope verified before unserialize.
 */
final class FileMetaCache implements MetaCacheInterface
{
    private const string ENVELOPE_VERSION = '3';

    private const string ENVELOPE_HMAC_ALGO = 'sha256';

    private readonly string $directory;

    private readonly string $signingKey;

    private readonly MemoryMetaCache $memory;

    private readonly MetaCacheAllowedClasses $allowedClasses;

    private readonly FileMetaSourceHash $sourceHash;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(
        string $directory,
        string $signingKey,
        int $memoryMaxEntries = 0,
        ?MetaCacheAllowedClasses $allowedClasses = null,
    ) {
        if ($directory === '' || ! is_dir($directory)) {
            throw new InvalidArgumentException('FileMetaCache directory must exist: ' . $directory);
        }

        if ($signingKey === '') {
            throw new InvalidArgumentException('FileMetaCache signing key must not be empty.');
        }

        $real = realpath($directory);
        if ($real === false) {
            throw new InvalidArgumentException('FileMetaCache directory is not resolvable: ' . $directory);
        }

        $this->directory = $real;
        $this->signingKey = $signingKey;
        $this->memory = new MemoryMetaCache($memoryMaxEntries);
        $this->allowedClasses = $allowedClasses ?? new MetaCacheAllowedClasses();
        $this->sourceHash = new FileMetaSourceHash();
    }

    public function get(string $className): ?ClassMeta
    {
        $cached = $this->memory->get($className);
        if ($cached !== null) {
            return $cached;
        }

        $path = $this->pathFor($className);
        if (! is_file($path)) {
            return null;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            return null;
        }

        $meta = $this->decodeEnvelope($raw, $className);
        if ($meta === null) {
            return null;
        }

        $this->memory->set($className, $meta);

        return $meta;
    }

    /**
     * @throws JsonException
     * @throws \Random\RandomException
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    public function set(string $className, ClassMeta $meta): void
    {
        if ($meta->className !== $className) {
            throw new InvalidArgumentException(
                'ClassMeta className must match cache key.',
            );
        }

        $path = $this->pathFor($className);
        $payload = $this->encodeEnvelope($className, $meta);

        $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $handle = fopen($tmp, 'cb');
        if ($handle === false) {
            throw new RuntimeException('Unable to open cache temp file: ' . $tmp);
        }

        try {
            if (! flock($handle, LOCK_EX)) {
                throw new RuntimeException('Unable to lock cache temp file: ' . $tmp);
            }

            $written = fwrite($handle, $payload);
            if ($written === false || $written !== strlen($payload)) {
                throw new RuntimeException('Unable to write cache temp file: ' . $tmp);
            }

            if (! fflush($handle)) {
                throw new RuntimeException('Unable to flush cache temp file: ' . $tmp);
            }

            flock($handle, LOCK_UN);
        } finally {
            fclose($handle);
        }

        if (! rename($tmp, $path)) {
            $this->deleteFile($tmp);
            throw new RuntimeException('Unable to publish cache file: ' . $path);
        }

        $this->memory->set($className, $meta);
    }

    public function has(string $className): bool
    {
        return $this->get($className) !== null;
    }

    public function clear(?string $className = null): void
    {
        $this->memory->clear($className);

        if ($className !== null) {
            $this->deleteFile($this->pathFor($className));

            return;
        }

        $files = glob($this->directory . '/*.cache');
        if ($files === false) {
            $files = [];
        }

        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    private function deleteFile(string $path): void
    {
        if (! is_file($path)) {
            return;
        }

        unlink($path);
    }

    private function pathFor(string $className): string
    {
        $key = hash('xxh3', $this->signingKey . "\0" . $className);

        return $this->directory . '/' . $key . '.cache';
    }

    /**
     * @throws JsonException
     */
    private function encodeEnvelope(string $className, ClassMeta $meta): string
    {
        $serialized = serialize($meta);
        $hash = hash_hmac(self::ENVELOPE_HMAC_ALGO, $serialized, $this->signingKey);

        return json_encode([
            'v' => self::ENVELOPE_VERSION,
            'class' => $className,
            'source' => $this->sourceHash->ofClass($className) ?? '',
            'hash' => $hash,
            'payload' => base64_encode($serialized),
        ], JSON_THROW_ON_ERROR);
    }

    private function decodeEnvelope(string $raw, string $expectedClass): ?ClassMeta
    {
        $envelope = $this->parseEnvelopeJson($raw);
        if ($envelope === null || ! $this->envelopeMatchesClass($envelope, $expectedClass)) {
            return null;
        }

        $serialized = $this->verifiedSerializedPayload($envelope);
        if ($serialized === null || ! $this->sourceHash->matchesEnvelope($envelope, $expectedClass)) {
            return null;
        }

        return $this->unserializeClassMeta($serialized, $expectedClass);
    }

    /**
     * @return array{v?:string,class?:string,source?:string,hash?:string,payload?:string}|null
     */
    private function parseEnvelopeJson(string $raw): ?array
    {
        try {
            /** @var array{v?:string,class?:string,source?:string,hash?:string,payload?:string} $envelope */
            $envelope = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        if (($envelope['v'] ?? null) !== self::ENVELOPE_VERSION) {
            return null;
        }

        return $envelope;
    }

    /**
     * @param  array{v?:string,class?:string,source?:string,hash?:string,payload?:string}  $envelope
     */
    private function envelopeMatchesClass(array $envelope, string $expectedClass): bool
    {
        return ($envelope['class'] ?? null) === $expectedClass;
    }

    /**
     * @param  array{v?:string,class?:string,source?:string,hash?:string,payload?:string}  $envelope
     */
    private function verifiedSerializedPayload(array $envelope): ?string
    {
        $payload = $envelope['payload'] ?? null;
        $hash = $envelope['hash'] ?? null;
        if (! is_string($payload) || ! is_string($hash)) {
            return null;
        }

        $serialized = base64_decode($payload, true);
        if ($serialized === false) {
            return null;
        }

        $expected = hash_hmac(self::ENVELOPE_HMAC_ALGO, $serialized, $this->signingKey);
        if (! hash_equals($expected, $hash)) {
            return null;
        }

        return $serialized;
    }

    private function unserializeClassMeta(string $serialized, string $expectedClass): ?ClassMeta
    {
        $meta = unserialize($serialized, ['allowed_classes' => $this->allowedClasses->all()]);
        if (! $meta instanceof ClassMeta) {
            return null;
        }

        if ($meta->className !== $expectedClass) {
            return null;
        }

        return $meta;
    }
}
