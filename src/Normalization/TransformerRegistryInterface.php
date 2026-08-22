<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

interface TransformerRegistryInterface
{
    public function register(TransformerInterface $transformer): void;

    public function transform(mixed $value): mixed;
}
