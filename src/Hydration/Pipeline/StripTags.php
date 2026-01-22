<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Pipeline;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Hydration\PipelineStepInterface;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Strip HTML and PHP tags from string values.
 */
final readonly class StripTags implements PipelineStepInterface
{
    /**
     * @param  string|null  $allowedTags  Optional list of tags to allow (e.g., '<p><a>')
     */
    public function __construct(
        private ?string $allowedTags = null,
    ) {}

    public function process(mixed $value, PropertyMeta $property, ?Context $ctx): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        return $this->allowedTags !== null
            ? strip_tags($value, $this->allowedTags)
            : strip_tags($value);
    }
}
