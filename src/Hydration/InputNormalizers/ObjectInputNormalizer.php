<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizerInterface;

final class ObjectInputNormalizer implements InputNormalizerInterface
{
    public function supports(mixed $source): bool
    {
        return is_object($source);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     */
    public function normalize(mixed $source): array
    {
        if (! is_object($source)) {
            throw new HydrationException(
                message: 'Object input expected.',
                givenType: get_debug_type($source),
            );
        }

        /** @var array<string, mixed> $vars */
        $vars = get_object_vars($source);

        return $vars;
    }
}
