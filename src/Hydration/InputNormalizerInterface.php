<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

interface InputNormalizerInterface
{
    public function supports(mixed $input): bool;

    /**
     * @return array<string, mixed>
     */
    public function normalize(mixed $input): array;
}
