<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

class MappingException extends JdtoException
{
    public static function missingRequiredKey(string $key, string $path = ''): self
    {
        return new self(
            message: "Missing required key '{$key}'",
            path: $path,
        );
    }

    public static function invalidMapping(string $sourceKey, string $targetProperty, string $path = ''): self
    {
        return new self(
            message: "Cannot map source key '{$sourceKey}' to property '{$targetProperty}'",
            path: $path,
        );
    }
}
