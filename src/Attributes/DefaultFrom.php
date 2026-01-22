<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;
use InvalidArgumentException;

/**
 * Specify default value source for a property.
 *
 * Security: Only static methods on the DTO class itself are allowed to prevent arbitrary code execution.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class DefaultFrom
{
    /**
     * @param  string|null  $config  Config key (e.g., 'app.timezone')
     * @param  string|null  $env  Environment variable name
     * @param  string|null  $method  Static method on the DTO class (e.g., 'generateDefault')
     */
    public function __construct(
        public ?string $config = null,
        public ?string $env = null,
        public ?string $method = null,
    ) {
        if ($config === null && $env === null && $method === null) {
            throw new InvalidArgumentException(
                'Must specify at least one of: config, env, method',
            );
        }

        // Security: Only allow safe method names (alphanumeric and underscore)
        if ($method !== null && ! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $method)) {
            throw new InvalidArgumentException(
                "Invalid method name: {$method}. Only alphanumeric and underscore allowed.",
            );
        }
    }
}
