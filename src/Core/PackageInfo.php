<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

/**
 * Package version marker used by the Phase 1 scaffold and smoke tests.
 * Replaced by the full DTO/Data hierarchy in Phase 2.
 */
final class PackageInfo
{
    public const string NAME = 'jooservices/dto';

    public const string VERSION = '3.0.0';

    public static function name(): string
    {
        return self::NAME;
    }

    public static function version(): string
    {
        return self::VERSION;
    }
}
