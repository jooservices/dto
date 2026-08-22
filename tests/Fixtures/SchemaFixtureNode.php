<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

/**
 * Self-referencing node used to exercise cyclic $ref generation in the schema generators.
 */
final class SchemaFixtureNode extends Dto
{
    public function __construct(
        public readonly string $label,
        public readonly ?self $parent = null,
    ) {
    }
}
