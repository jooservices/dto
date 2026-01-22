<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use Exception;
use Throwable;

/**
 * @phpstan-consistent-constructor
 */
class JdtoException extends Exception
{
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
    }

    public function withPath(string $path): static
    {
        /** @var static */
        return new static(
            $this->message,
            $path,
            $this->expectedType,
            $this->givenType,
            $this->givenValue,
            (int) $this->getCode(),
            $this->getPrevious(),
        );
    }

    public function prependPath(string $segment): static
    {
        $newPath = $this->path === '' ? $segment : $segment.'.'.$this->path;

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
            $message .= " at path '{$this->path}'";
        }

        if ($this->expectedType !== null && $this->givenType !== null) {
            $message .= " (expected: {$this->expectedType}, given: {$this->givenType})";
        }

        return $message;
    }
}
