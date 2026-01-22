<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Hydration\InputNormalizerInterface;

final class ArrayInputNormalizer implements InputNormalizerInterface
{
    public function supports(mixed $input): bool
    {
        return is_array($input);
    }

    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $input): array
    {
        if (! is_array($input)) {
            return [];
        }

        /** @var array<string, mixed> $input */
        return $input;
    }
}
