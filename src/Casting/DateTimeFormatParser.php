<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use DateTimeImmutable;
use ValueError;

final class DateTimeFormatParser
{
    /**
     * @throws ValueError
     */
    public function parse(string $format, string $value): ?DateTimeImmutable
    {
        $candidates = [$value];
        $zulu = $this->zuluToOffset($value);
        if ($zulu !== null) {
            $candidates[] = $zulu;
        }

        foreach ($candidates as $candidate) {
            $parsed = date_create_immutable_from_format($format, $candidate);
            if ($parsed === false) {
                continue;
            }

            if (! $this->parsedCleanly($parsed, $format, $candidate)) {
                continue;
            }

            return $parsed;
        }

        return null;
    }

    private function zuluToOffset(string $value): ?string
    {
        if (preg_match('/Z$/i', $value) !== 1) {
            return null;
        }

        return substr($value, 0, -1) . '+00:00';
    }

    private function parsedCleanly(DateTimeImmutable $parsed, string $format, string $value): bool
    {
        $errors = date_get_last_errors();
        if ($errors !== false) {
            if ($errors['warning_count'] > 0 || $errors['error_count'] > 0) {
                return false;
            }
        }

        return $parsed->format($format) === $value;
    }
}
