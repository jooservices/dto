<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core\Traits;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Engine\EngineFactory;

trait NormalizesToOutput
{
    private static ?Engine $normalizerEngine = null;

    public static function setNormalizerEngine(Engine $engine): void
    {
        self::$normalizerEngine = $engine;
    }

    public static function resetNormalizerEngine(): void
    {
        self::$normalizerEngine = null;
    }

    private static function getNormalizerEngine(): Engine
    {
        if (self::$normalizerEngine === null) {
            self::$normalizerEngine = new EngineFactory()->create();
        }

        return self::$normalizerEngine;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(?Context $ctx = null): array
    {
        $result = self::getNormalizerEngine()->normalize($this, $ctx);

        // Apply wrapping if configured
        $wrap = $ctx?->getSerializationOptions()->wrap;

        if ($wrap !== null) {
            return [$wrap => $result];
        }

        return $result;
    }

    public function toJson(int $flags = 0, ?Context $ctx = null): string
    {
        return self::getNormalizerEngine()->normalizeToJson($this, $ctx, $flags);
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
