<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

final readonly class RuleViolation
{
    public function __construct(
        private string $propertyName,
        private string $ruleName,
        private string $message,
        private mixed $invalidValue = null,
        /** @var array<string, mixed> */
        private array $parameters = [],
    ) {}

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    public function getRuleName(): string
    {
        return $this->ruleName;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getInvalidValue(): mixed
    {
        return $this->invalidValue;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function getParameter(string $name, mixed $default = null): mixed
    {
        return $this->parameters[$name] ?? $default;
    }

    public function getFormattedMessage(): string
    {
        return "[{$this->propertyName}] {$this->ruleName}: {$this->message}";
    }
}
