<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Hydration\InputNormalizerInterface;
use stdClass;

final class ObjectInputNormalizer implements InputNormalizerInterface
{
    public function supports(mixed $input): bool
    {
        return is_object($input);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $input): array
    {
        if (! is_object($input)) {
            return [];
        }

        if ($input instanceof stdClass) {
            /** @var array<string, mixed> */
            return (array) $input;
        }

        /** @var array<string, mixed> */
        return get_object_vars($input);
    }
}
