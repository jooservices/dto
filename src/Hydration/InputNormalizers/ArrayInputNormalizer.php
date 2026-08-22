<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Hydration\InputNormalizerInterface;

final class ArrayInputNormalizer implements InputNormalizerInterface
{
    public function supports(mixed $source): bool
    {
        return is_array($source);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $source): array
    {
        /** @var array<string, mixed> $source */
        return $source;
    }
}
