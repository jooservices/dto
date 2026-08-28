<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CastingFixtureUntypedArrayDto extends Dto
{
    /**
     * Untyped on purpose: no `@var` / `@param` array item type.
     *
     * @phpstan-ignore missingType.iterableValue
     */
    public function __construct(
        public readonly array $categories = [],
    ) {
    }
}
