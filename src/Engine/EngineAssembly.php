<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Meta\MetaCacheInterface;

final class EngineAssembly
{
    /**
     * @throws InvalidArgumentException
     */
    public function build(?MetaCacheInterface $cache = null): Engine
    {
        $parts = (new EngineBuildFactory())->create($cache);

        return new Engine(
            new EngineHydratorService(
                $parts->metaFactory,
                $parts->hydrator,
                (new InputNormalizerFactory())->create(),
            ),
            new EngineNormalizerService($parts->normalizer),
            $parts->validatorService,
            $parts->copyWithApplier,
            $parts->defaults,
        );
    }
}
