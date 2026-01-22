<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizerInterface;
use JsonException;

use const JSON_THROW_ON_ERROR;

final class JsonInputNormalizer implements InputNormalizerInterface
{
    public function supports(mixed $input): bool
    {
        if (! is_string($input)) {
            return false;
        }

        $trimmed = trim($input);

        return str_starts_with($trimmed, '{') || str_starts_with($trimmed, '[');
    }

    public function normalize(mixed $input): array
    {
        if (! is_string($input)) {
            return [];
        }

        try {
            $decoded = json_decode($input, true, 512, JSON_THROW_ON_ERROR);

            if (! is_array($decoded)) {
                throw new HydrationException('JSON must decode to an array or object');
            }

            /** @var array<string, mixed> $decoded */
            return $decoded;
        } catch (JsonException $e) {
            throw new HydrationException(
                message: 'Invalid JSON input: '.$e->getMessage(),
                previous: $e,
            );
        }
    }
}
