<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Exceptions\HydrationException;

interface InputNormalizerInterface
{
    public function supports(mixed $source): bool;

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     */
    public function normalize(mixed $source): array;
}
