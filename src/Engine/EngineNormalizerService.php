<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Normalization\NormalizerInterface;
use JsonException;
use ReflectionException;

final class EngineNormalizerService
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function normalize(object $instance, ?Context $ctx): array
    {
        return $this->normalizer->normalize($instance, $ctx);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function normalizeToJson(object $instance, ?Context $ctx, int $flags): string
    {
        $flags = $flags === 0 ? JSON_THROW_ON_ERROR : ($flags | JSON_THROW_ON_ERROR);
        $encoded = json_encode($this->normalize($instance, $ctx), $flags);
        if ($encoded === false) {
            throw new JsonException(json_last_error_msg());
        }

        return $encoded;
    }
}
