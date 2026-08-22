<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

/**
 * Minimal #[CastWith] target used to exercise CastWithExecutor without
 * involving the built-in scalar casters.
 */
final class CastingFixtureUppercaseCaster implements CasterInterface
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
