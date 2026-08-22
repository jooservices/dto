<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizerInterface;

final class ChainInputNormalizer implements InputNormalizerInterface
{
    /**
     * @param  list<InputNormalizerInterface>  $normalizers
     */
    public function __construct(
        private readonly array $normalizers,
    ) {
    }

    public function supports(mixed $source): bool
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supports($source)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     */
    public function normalize(mixed $source): array
    {
        foreach ($this->normalizers as $normalizer) {
            if ($normalizer->supports($source)) {
                return $normalizer->normalize($source);
            }
        }

        throw new HydrationException(
            message: 'Unsupported input type for hydration.',
            givenType: get_debug_type($source),
            givenValue: $source,
        );
    }
}
