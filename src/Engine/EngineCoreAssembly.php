<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Hydration\Hydrator;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaCacheInterface;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Normalizer;
use JOOservices\Dto\Validation\ObjectValidator;

final class EngineCoreAssembly
{
    /**
     * @return array{metaFactory: MetaFactory, validator: ObjectValidator, hydrator: Hydrator, normalizer: Normalizer}
     *
     * @throws InvalidArgumentException
     */
    public function build(?MetaCacheInterface $cache = null): array
    {
        $cache ??= new MemoryMetaCache();
        $metaFactory = new MetaFactory($cache);
        $validator = (new ValidatorBootstrap())->create();

        $defaults = new SharedDefaults();

        return [
            'metaFactory' => $metaFactory,
            'validator' => $validator,
            'hydrator' => (new HydratorFactory())->create($metaFactory, $validator, $defaults),
            'normalizer' => (new NormalizerFactory())->create($metaFactory, $defaults->looseContext()),
        ];
    }
}
