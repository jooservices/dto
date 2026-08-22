<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Support\CachedOptionedInstantiator;

/**
 * Process-wide Engine holder. One Engine per process; cache ClassMeta, not Engines per class.
 */
final class EngineHolder
{
    private static ?EngineInterface $engine = null;

    /**
     * @throws InvalidArgumentException
     */
    public function current(): EngineInterface
    {
        if (self::$engine instanceof EngineInterface) {
            return self::$engine;
        }

        $factory = new EngineFactory();
        self::$engine = $factory->create();

        return self::$engine;
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function get(): EngineInterface
    {
        return (new self())->current();
    }

    public static function set(EngineInterface $engine): void
    {
        self::$engine = $engine;
    }

    public static function reset(): void
    {
        self::$engine = null;
        (new CachedOptionedInstantiator())->clearCache();
    }
}
