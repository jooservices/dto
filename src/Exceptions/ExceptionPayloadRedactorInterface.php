<?php

declare(strict_types=1);

namespace JOOservices\Dto\Exceptions;

/**
 * Redacts sensitive values before they appear in exception payloads.
 */
interface ExceptionPayloadRedactorInterface
{
    /**
     * @param  string  $path  Property or exception path for context
     */
    public function redact(string $path, mixed $value): mixed;
}
