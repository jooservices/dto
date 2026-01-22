<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Engine\Engine;
use JOOservices\Dto\Hydration\InputNormalizerInterface;
use stdClass;

final class SpyEngine extends Engine
{
    public int $hydrateCalls = 0;

    public int $normalizeCalls = 0;

    public ?object $hydrateResult = null;

    public array $normalizeResult = [];

    public function __construct()
    {
        // Don't call parent constructor to avoid dependencies
    }

    public function addInputNormalizer(InputNormalizerInterface $normalizer): self
    {
        return $this;
    }

    public function hydrate(string $class, mixed $source, ?Context $ctx = null): object
    {
        $this->hydrateCalls++;

        return $this->hydrateResult ?? new stdClass;
    }

    public function normalize(object $instance, ?Context $ctx = null): array
    {
        $this->normalizeCalls++;

        return $this->normalizeResult;
    }

    public function normalizeToJson(object $instance, ?Context $ctx = null, int $flags = 0): string
    {
        return json_encode($this->normalize($instance, $ctx), $flags | JSON_THROW_ON_ERROR);
    }
}
