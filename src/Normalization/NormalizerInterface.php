<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use ReflectionException;

interface NormalizerInterface
{
    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function normalize(object $instance, ?Context $ctx): array;
}
