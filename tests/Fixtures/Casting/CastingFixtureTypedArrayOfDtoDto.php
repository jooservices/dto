<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures\Casting;

use JOOservices\Dto\Core\Dto;

/**
 * Lives one namespace level below tests/Fixtures (rather than alongside
 * AddressDto) so the `@var array<...>` item type below must reference
 * AddressDto by its fully-qualified name. Pint's fully_qualified_strict_types
 * rule shortens same-namespace/child-namespace FQCNs down to a bare or
 * relative name, which our DocBlockArrayParser (a plain regex, not a real
 * namespace-aware resolver) cannot re-expand at runtime.
 */
final class CastingFixtureTypedArrayOfDtoDto extends Dto
{
    public function __construct(
        /** @var array<\JOOservices\Dto\Tests\Fixtures\AddressDto> */
        public readonly array $addresses,
    ) {
    }
}
