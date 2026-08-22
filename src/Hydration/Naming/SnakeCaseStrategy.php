<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Naming;

final class SnakeCaseStrategy implements NamingStrategyInterface
{
    public function toProperty(string $sourceKey): string
    {
        return (new CamelCaseStrategy())->toProperty($sourceKey);
    }

    public function toSource(string $propertyName): string
    {
        return (new CamelCaseStrategy())->toSource($propertyName);
    }
}
