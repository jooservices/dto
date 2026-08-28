<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use ReflectionClass;

final class FileMetaSourceHash
{
    public function ofClass(string $className): ?string
    {
        if (! class_exists($className)) {
            return null;
        }

        $fileName = (new ReflectionClass($className))->getFileName();
        if ($fileName === false || ! is_readable($fileName)) {
            return null;
        }

        $contents = file_get_contents($fileName);
        if ($contents === false) {
            return null;
        }

        return hash('sha256', $contents);
    }

    /**
     * @param  array{v?:string,class?:string,source?:string,hash?:string,payload?:string}  $envelope
     */
    public function matchesEnvelope(array $envelope, string $className): bool
    {
        $current = $this->ofClass($className);
        if ($current === null) {
            return true;
        }

        $stored = $envelope['source'] ?? null;

        return is_string($stored) && $stored !== '' && hash_equals($current, $stored);
    }
}
