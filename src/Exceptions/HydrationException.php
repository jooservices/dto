<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

use Throwable;

class HydrationException extends JdtoException
{
    /** @var array<JdtoException> */
    private array $errors = [];

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
     * @param  array<JdtoException>  $errors
     */
    public static function fromErrors(string $message, array $errors, string $path = ''): self
    {
        $exception = new self($message, $path);
        $exception->addErrors($errors);

        return $exception;
    }

    public function addError(JdtoException $error): self
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * @param  array<JdtoException>  $errors
     */
    public function addErrors(array $errors): self
    {
        foreach ($errors as $error) {
            $this->addError($error);
        }

        return $this;
    }

    /**
     * @return array<JdtoException>
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasNestedErrors(): bool
    {
        return $this->errors !== [];
    }

    public function getErrorCount(): int
    {
        return count($this->errors);
    }

    public function getFullMessage(): string
    {
        $message = parent::getFullMessage();

        if ($this->hasNestedErrors()) {
            $message .= ' ['.$this->getErrorCount().' nested error(s)]';

            foreach ($this->errors as $index => $error) {
                $message .= "\n  [{$index}] ".$error->getFullMessage();
            }
        }

        return $message;
    }
}
