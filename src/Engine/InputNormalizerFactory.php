<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Hydration\InputNormalizers\ArrayInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\ChainInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\JsonInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\ObjectInputNormalizer;

final class InputNormalizerFactory
{
    public function create(): ChainInputNormalizer
    {
        return new ChainInputNormalizer([
            new ArrayInputNormalizer(),
            new JsonInputNormalizer(),
            new ObjectInputNormalizer(),
        ]);
    }
}
