<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use RuntimeException;
use Throwable;

/**
 * Base exception for jooservices/dto (vendorized — zero runtime deps).
 *
 * @phpstan-consistent-constructor
 */
class DtoException extends RuntimeException
{
    private ?ExceptionPayloadRedactorInterface $payloadRedactor = null;

    /**
     * @var array<string, mixed>
     */
    private array $context = [];

    public function __construct(
        string $message,
        public readonly string $path = '',
        public readonly ?string $expectedType = null,
        public readonly ?string $givenType = null,
        public readonly mixed $givenValue = null,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);

        if ($this->path !== '') {
            $this->context['path'] = $this->path;
        }

        if ($this->expectedType !== null) {
            $this->context['expectedType'] = $this->expectedType;
        }

        if ($this->givenType !== null) {
            $this->context['givenType'] = $this->givenType;
        }
    }

    public function withPayloadRedactor(?ExceptionPayloadRedactorInterface $redactor): static
    {
        /** @var static $copy */
        $copy = new static(
            $this->message,
            $this->path,
            $this->expectedType,
            $this->givenType,
            $this->givenValue,
            (int) $this->getCode(),
            $this->getPrevious(),
        );
        $copy->payloadRedactor = $redactor;
        $copy->context = $this->context;

        return $copy;
    }

    public function withPath(string $path): static
    {
        /** @var static $copy */
        $copy = new static(
            $this->message,
            $path,
            $this->expectedType,
            $this->givenType,
            $this->givenValue,
            (int) $this->getCode(),
            $this->getPrevious(),
        );
        $copy->payloadRedactor = $this->payloadRedactor;
        $copy->context = $this->context;

        if ($path === '') {
            unset($copy->context['path']);

            return $copy;
        }

        $copy->context['path'] = $path;

        return $copy;
    }

    public function prependPath(string $segment): static
    {
        $newPath = $this->path === '' ? $segment : $segment . '.' . $this->path;

        return $this->withPath($newPath);
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getFullMessage(): string
    {
        $message = $this->message;

        if ($this->path !== '') {
            $message .= ' (path: ' . $this->path . ')';
        }

        if ($this->expectedType !== null) {
            $message .= ' (expected: ' . $this->expectedType . ')';
        }

        if ($this->givenType !== null) {
            $message .= ' (given: ' . $this->givenType . ')';
        }

        return $message;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'message' => $this->message,
            'path' => $this->path,
            'expectedType' => $this->expectedType,
            'givenType' => $this->givenType,
            'givenValue' => $this->payloadRedactor !== null
                ? $this->payloadRedactor->redact($this->path, $this->givenValue)
                : (new ExceptionGivenValuePresenter())->present($this->givenValue),
            'context' => $this->context,
        ];

        return $payload;
    }
}
