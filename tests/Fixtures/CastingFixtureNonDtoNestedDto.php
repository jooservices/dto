<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;
use stdClass;

/**
 * Holds a plain (non-DTO) class-typed property, used to exercise
 * ValueTypeCaster::castClass's "nested type is not a DTO" branch.
 */
final class CastingFixtureNonDtoNestedDto extends Dto
{
    public function __construct(
        public readonly stdClass $payload,
    ) {
    }
}
