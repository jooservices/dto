<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

/**
 * Safe default presentation of user-supplied values in exception payloads.
 */
final class ExceptionGivenValuePresenter
{
    private const int MAX_STRING_BYTES = 256;

    private const int MAX_ARRAY_ITEMS = 20;

    private const int MAX_DEPTH = 4;

    public function present(mixed $value, int $depth = 0): mixed
    {
        if ($depth > self::MAX_DEPTH) {
            return '[truncated: max depth]';
        }

        if (is_string($value)) {
            return $this->truncateString($value);
        }

        if (is_array($value)) {
            return $this->presentArray($value, $depth);
        }

        if (is_object($value)) {
            return '[object ' . $value::class . ']';
        }

        return $value;
    }

    private function truncateString(string $value): string
    {
        if (strlen($value) <= self::MAX_STRING_BYTES) {
            return $value;
        }

        return substr($value, 0, self::MAX_STRING_BYTES) . '…[' . strlen($value) . ' bytes]';
    }

    /**
     * @param  array<mixed>  $value
     * @return array<mixed>
     */
    private function presentArray(array $value, int $depth): array
    {
        $out = [];
        $count = 0;
        foreach ($value as $key => $item) {
            if ($count >= self::MAX_ARRAY_ITEMS) {
                $out['…'] = '[truncated: max items]';
                break;
            }

            $out[(string) $key] = $this->present($item, $depth + 1);
            ++$count;
        }

        return $out;
    }
}
