<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

final class CasterRegistry implements CasterRegistryInterface
{
    /** @var list<CasterInterface> */
    private array $casters = [];

    public function register(CasterInterface $caster): void
    {
        $this->casters[] = $caster;
    }

    public function firstMatching(
        TypeDescriptor $type,
        PropertyMeta $property,
        mixed $value,
        ?Context $ctx,
    ): ?CasterInterface {
        foreach ($this->casters as $caster) {
            if ($caster->supports($type, $property, $value, $ctx)) {
                return $caster;
            }
        }

        return null;
    }
}
