<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

/**
 * Validates URL schemes for {@see Validators\UrlValidator}.
 */
final class UrlSchemeGuard
{
    /**
     * @param  list<string>  $allowedSchemes
     */
    public function isAllowed(string $value, array $allowedSchemes): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);
        if (! is_string($scheme) || $scheme === '') {
            return false;
        }

        $normalized = strtolower($scheme);

        foreach ($allowedSchemes as $allowed) {
            if (strtolower($allowed) === $normalized) {
                return true;
            }
        }

        return false;
    }
}
