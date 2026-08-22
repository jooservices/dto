<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use Throwable;

final class ValidationException extends DtoException
{
    /**
     * @param  list<RuleViolation>  $violations
     */
    public function __construct(
        string $message,
        string $path = '',
        ?string $expectedType = null,
        ?string $givenType = null,
        mixed $givenValue = null,
        int $code = 0,
        ?Throwable $previous = null,
        private array $violations = [],
    ) {
        parent::__construct($message, $path, $expectedType, $givenType, $givenValue, $code, $previous);
    }

    /**
     * @return list<RuleViolation>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = parent::toArray();
        $payload['violations'] = array_map(
            static fn(RuleViolation $violation): array => $violation->toArray(),
            $this->violations,
        );

        return $payload;
    }
}
