<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaCacheInterface;
use JOOservices\Dto\Meta\MetaFactory;

final class EngineBuildFactory
{
    /**
     * @throws InvalidArgumentException
     */
    public function create(?MetaCacheInterface $cache = null): EngineBuildParts
    {
        $cache ??= new MemoryMetaCache();
        $defaults = new SharedDefaults();
        $metaFactory = new MetaFactory($cache);
        $validator = (new ValidatorBootstrap())->create();
        $hydrator = (new HydratorFactory())->create($metaFactory, $validator, $defaults);
        $normalizer = (new NormalizerFactory())->create($metaFactory, $defaults->looseContext());

        return new EngineBuildParts(
            metaFactory: $metaFactory,
            hydrator: $hydrator,
            normalizer: $normalizer,
            defaults: $defaults,
            validatorService: new EngineValidatorService($metaFactory, $validator),
            copyWithApplier: new EngineCopyWithApplier($metaFactory, $hydrator),
        );
    }
}
