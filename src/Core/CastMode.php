<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

/**
 * Supported cast-mode values for {@see Context}.
 */
enum CastMode: string
{
    case Loose = 'loose';
    case Strict = 'strict';
    case Permissive = 'permissive';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(
            static fn(self $mode): string => $mode->value,
            self::cases(),
        );
    }
}
