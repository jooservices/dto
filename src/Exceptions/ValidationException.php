<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use Throwable;

class ValidationException extends JdtoException
{
    /** @var array<RuleViolation> */
    private array $violations = [];

    public function __construct(
        string $message,
        string $path = '',
        ?string $expectedType = null,
        ?string $givenType = null,
        mixed $givenValue = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $path, $expectedType, $givenType, $givenValue, $code, $previous);
    }

    /**
     * @param  array<RuleViolation>  $violations
     */
    public static function fromViolations(string $message, array $violations, string $path = ''): self
    {
        $exception = new self($message, $path);
        $exception->addViolations($violations);

        return $exception;
    }

    public function addViolation(RuleViolation $violation): self
    {
        $this->violations[] = $violation;

        return $this;
    }

    /**
     * @param  array<RuleViolation>  $violations
     */
    public function addViolations(array $violations): self
    {
        foreach ($violations as $violation) {
            $this->addViolation($violation);
        }

        return $this;
    }

    /**
     * @return array<RuleViolation>
     */
    public function getViolations(): array
    {
        return $this->violations;
    }

    public function hasViolations(): bool
    {
        return $this->violations !== [];
    }

    public function getViolationCount(): int
    {
        return count($this->violations);
    }

    public function getFullMessage(): string
    {
        $message = parent::getFullMessage();

        if ($this->hasViolations()) {
            $message .= ' ['.$this->getViolationCount().' violation(s)]';

            foreach ($this->violations as $violation) {
                $message .= "\n  - ".$violation->getMessage();
            }
        }

        return $message;
    }
}
