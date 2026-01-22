<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Naming;

final class CamelCaseStrategy implements NamingStrategyInterface
{
    public function convert(string $name, string $direction): string
    {
        return match ($direction) {
            self::DIRECTION_TO_SOURCE => $this->toCamelCase($name),
            self::DIRECTION_TO_PROPERTY => $this->toCamelCase($name),
            default => $name,
        };
    }

    private function toCamelCase(string $name): string
    {
        if (str_contains($name, '_')) {
            return $this->fromSnakeCase($name);
        }

        if (str_contains($name, '-')) {
            return $this->fromKebabCase($name);
        }

        return lcfirst($name);
    }

    private function fromSnakeCase(string $name): string
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

    private function fromKebabCase(string $name): string
    {
        $words = explode('-', $name);
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
