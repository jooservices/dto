<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Naming;

final class SnakeCaseStrategy implements NamingStrategyInterface
{
    public function convert(string $name, string $direction): string
    {
        return match ($direction) {
            self::DIRECTION_TO_SOURCE => $this->toSnakeCase($name),
            self::DIRECTION_TO_PROPERTY => $this->toCamelCase($name),
            default => $name,
        };
    }

    private function toSnakeCase(string $name): string
    {
        // Handle lowercase followed by uppercase (camelCase)
        $result = preg_replace('/([a-z])([A-Z])/', '$1_$2', $name);
        // Handle uppercase followed by uppercase+lowercase (e.g., HTMLParser -> HTML_Parser)
        $result = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $result ?? $name);
        // Handle digits followed by uppercase letter only (field2Name -> field2_Name)
        $result = preg_replace('/([0-9])([A-Z])/', '$1_$2', $result ?? $name);

        if ($result === null) {
            return strtolower($name);
        }

        return strtolower($result);
    }

    private function toCamelCase(string $name): string
    {
        $words = explode('_', $name);
        $first = array_shift($words);

        if ($first === null) {
            return $name;
        }

        $rest = array_map(
            static fn (string $word): string => ucfirst(strtolower($word)),
            $words,
        );

        return strtolower($first).implode('', $rest);
    }
}
