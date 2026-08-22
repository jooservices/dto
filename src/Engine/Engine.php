<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SharedDefaults;

final class Engine implements EngineInterface
{
    public function __construct(
        private readonly EngineHydratorService $hydrator,
        private readonly EngineNormalizerService $normalizer,
        private readonly EngineValidatorService $validator,
        private readonly EngineCopyWithApplier $copyWith,
        private readonly SharedDefaults $defaults,
    ) {
    }

    /** @inheritDoc */
    public function hydrate(string $class, mixed $source, ?Context $ctx = null): object
    {
        return $this->hydrator->hydrate($class, $source, $ctx ?? $this->defaults->looseContext());
    }

    /** @inheritDoc */
    public function normalize(object $instance, ?Context $ctx = null): array
    {
        return $this->normalizer->normalize($instance, $ctx);
    }

    /** @inheritDoc */
    public function normalizeToJson(object $instance, ?Context $ctx = null, int $flags = 0): string
    {
        return $this->normalizer->normalizeToJson($instance, $ctx, $flags);
    }

    /** @inheritDoc */
    public function normalizeInput(mixed $source): array
    {
        return $this->hydrator->normalizeInput($source);
    }

    /** @inheritDoc */
    public function validate(object $instance, ?Context $ctx = null): void
    {
        $this->validator->validate($instance, $ctx ?? $this->defaults->validationContext());
    }

    /** @inheritDoc */
    public function copyWith(object $instance, array $patch, ?Context $ctx): object
    {
        return $this->copyWith->apply($instance, $patch, $ctx ?? $this->defaults->looseContext());
    }
}
