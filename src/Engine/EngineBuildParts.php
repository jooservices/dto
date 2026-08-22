<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Hydration\Hydrator;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Normalizer;

/**
 * @internal
 */
final readonly class EngineBuildParts
{
    public function __construct(
        public MetaFactory $metaFactory,
        public Hydrator $hydrator,
        public Normalizer $normalizer,
        public SharedDefaults $defaults,
        public EngineValidatorService $validatorService,
        public EngineCopyWithApplier $copyWithApplier,
    ) {
    }
}
