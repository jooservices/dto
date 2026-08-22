<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use Throwable;

final class HydrationException extends DtoException
{
    public const string CONSTRUCTOR_REQUIRED
        = 'DTO classes must declare a constructor with public promoted properties. '
        . 'Constructor-less DTOs are not supported.';

    /**
     * @param  list<DtoException>  $errors
     */
    public function __construct(
        string $message,
        string $path = '',
        ?string $expectedType = null,
        ?string $givenType = null,
        mixed $givenValue = null,
        int $code = 0,
        ?Throwable $previous = null,
        private readonly array $errors = [],
    ) {
        parent::__construct($message, $path, $expectedType, $givenType, $givenValue, $code, $previous);
    }

    /**
     * @return list<DtoException>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = parent::toArray();
        $payload['errors'] = array_map(
            static fn(DtoException $error): array => $error->toArray(),
            $this->errors,
        );

        return $payload;
    }
}
