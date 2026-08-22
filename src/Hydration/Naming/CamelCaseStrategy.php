<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Naming;

final class CamelCaseStrategy implements NamingStrategyInterface
{
    public function toProperty(string $sourceKey): string
    {
        $parts = explode('_', $sourceKey);
        $head = array_shift($parts);
        if ($head === null) {
            return $sourceKey;
        }

        $tail = '';
        foreach ($parts as $part) {
            $tail .= ucfirst($part);
        }

        return $head . $tail;
    }

    public function toSource(string $propertyName): string
    {
        $converted = preg_replace('/[A-Z]/', '_$0', $propertyName);
        if (! is_string($converted)) {
            return $propertyName;
        }

        return strtolower(ltrim($converted, '_'));
    }
}
