<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Naming;

/**
 * Bidirectional naming between source keys and property names.
 */
interface NamingStrategyInterface
{
    public function toProperty(string $sourceKey): string;

    public function toSource(string $propertyName): string;
}
