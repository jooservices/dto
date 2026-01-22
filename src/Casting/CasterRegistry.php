<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting;

use JOOservices\Dto\Core\Concerns\SupportsDiscovery;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Registry for casters with priority support and auto-discovery.
 *
 * Supports:
 * - Priority-based registration
 * - Lazy sorting
 * - Auto-discovery from directories
 * - PSR-11 container integration for dependency injection
 *
 * @template-use SupportsDiscovery<CasterInterface>
 */
final class CasterRegistry implements CasterRegistryInterface
{
    /** @use SupportsDiscovery<CasterInterface> */
    use SupportsDiscovery;

    /** @var array<array{caster: CasterInterface, priority: int}> */
    private array $casters = [];

    private bool $sorted = true;

    public function register(CasterInterface $caster, int $priority = 0): void
    {
        $this->casters[] = [
            'caster' => $caster,
            'priority' => $priority,
        ];

        $this->sorted = false;
    }

    public function get(PropertyMeta $property, mixed $value): ?CasterInterface
    {
        $this->resolvePendingClasses();
        $this->ensureSorted();

        foreach ($this->casters as $item) {
            if ($item['caster']->supports($property, $value)) {
                return $item['caster'];
            }
        }

        return null;
    }

    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        $caster = $this->get($property, $value);

        if ($caster === null) {
            throw CastException::noCasterFound(
                $value,
                $property->type->name,
                $property->name,
            );
        }

        return $caster->cast($property, $value, $ctx);
    }

    public function canCast(PropertyMeta $property, mixed $value): bool
    {
        return $this->get($property, $value) !== null;
    }

    protected function getItemInterface(): string
    {
        return CasterInterface::class;
    }

    protected function doRegister(object $instance, int $priority): void
    {
        /** @var CasterInterface $instance */
        $this->register($instance, $priority);
    }

    private function ensureSorted(): void
    {
        if ($this->sorted) {
            return;
        }

        usort(
            $this->casters,
            static fn (array $first, array $second): int => $second['priority'] <=> $first['priority'],
        );

        $this->sorted = true;
    }
}
