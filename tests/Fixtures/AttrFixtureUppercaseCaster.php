<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

/**
 * Minimal CastWith caster: uppercases whatever comes in, ignoring normal type casting.
 */
final class AttrFixtureUppercaseCaster implements CasterInterface
{
    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($type, $property, $value, $ctx);

        return true;
    }

    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        unset($type, $property, $ctx);

        /** @phpstan-ignore cast.string */
        return strtoupper((string) $value);
    }
}
