<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core\Traits;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Engine\EngineFactory;

trait CreatesFromSource
{
    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data, ?Context $ctx = null): static
    {
        return self::getEngine()->hydrate(static::class, $data, $ctx);
    }

    public static function fromJson(string $json, ?Context $ctx = null): static
    {
        return self::getEngine()->hydrate(static::class, $json, $ctx);
    }

    public static function fromObject(object $source, ?Context $ctx = null): static
    {
        return self::getEngine()->hydrate(static::class, $source, $ctx);
    }

    public static function from(mixed $source, ?Context $ctx = null): static
    {
        return self::getEngine()->hydrate(static::class, $source, $ctx);
    }

    /**
     * Create a partial DTO with only specified fields hydrated.
     *
     * @param  array<string>  $fields
     */
    public static function partial(array $fields): \JOOservices\Dto\Core\PartialDtoBuilder
    {
        return new \JOOservices\Dto\Core\PartialDtoBuilder(static::class, $fields);
    }

    public static function setEngine(Engine $engine): void
    {
        self::engineInstance($engine);
    }

    public static function resetEngine(): void
    {
        self::engineInstance(null, true);
    }

    private static function getEngine(): Engine
    {
        $engine = self::engineInstance();

        if ($engine === null) {
            $engine = new EngineFactory()->create();
            self::engineInstance($engine);
        }

        return $engine;
    }

    private static function engineInstance(?Engine $newEngine = null, bool $reset = false): ?Engine
    {
        /** @var Engine|null $engine */
        static $engine = null;

        if ($reset) {
            $engine = null;

            return null;
        }

        if ($newEngine !== null) {
            $engine = $newEngine;
        }

        return $engine;
    }
}
