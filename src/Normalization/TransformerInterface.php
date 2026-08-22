<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

interface TransformerInterface
{
    public function supports(mixed $value): bool;

    public function transform(mixed $value): mixed;
}
