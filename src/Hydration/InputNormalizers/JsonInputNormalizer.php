<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\ExceptionGivenValuePresenter;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizerInterface;
use JsonException;

final class JsonInputNormalizer implements InputNormalizerInterface
{
    public const int DECODE_DEPTH = 32;

    public function __construct(
        private readonly ExceptionGivenValuePresenter $givenValuePresenter = new ExceptionGivenValuePresenter(),
    ) {
    }

    public function supports(mixed $source): bool
    {
        return is_string($source);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     */
    public function normalize(mixed $source): array
    {
        if (! is_string($source)) {
            throw new HydrationException(
                message: 'JSON input must be a string.',
                givenType: get_debug_type($source),
            );
        }

        try {
            $decoded = json_decode(
                $source,
                true,
                self::DECODE_DEPTH,
                JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING,
            );
        } catch (JsonException $exception) {
            throw new HydrationException(
                message: 'Invalid JSON input.',
                givenType: 'string',
                givenValue: $this->givenValuePresenter->present($source),
                previous: $exception,
            );
        }

        if (! is_array($decoded)) {
            throw new HydrationException(
                message: 'JSON input must decode to an object/array.',
                givenType: get_debug_type($decoded),
                givenValue: $this->givenValuePresenter->present($decoded),
            );
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }
}
