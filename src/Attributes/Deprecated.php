<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;

/**
 * Mark a property as deprecated with optional migration information.
 *
 * When a deprecated property is accessed or hydrated, a deprecation warning
 * will be triggered to help developers migrate to newer alternatives.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class Deprecated
{
    public function __construct(
        public string $message = 'This property is deprecated',
        public ?string $since = null,
        public ?string $useInstead = null,
    ) {}

    /**
     * Get the complete deprecation message including context.
     */
    public function getFullMessage(): string
    {
        $msg = $this->message;

        if ($this->since !== null) {
            $msg .= " (since {$this->since})";
        }

        if ($this->useInstead !== null) {
            $msg .= ". Use '{$this->useInstead}' instead";
        }

        return $msg;
    }
}
