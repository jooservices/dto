<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use JOOservices\Dto\Core\Concerns\SupportsDiscovery;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Registry for validators with priority support and auto-discovery.
 *
 * Supports:
 * - Priority-based registration
 * - Lazy sorting
 * - Collects all violations before throwing
 * - Auto-discovery from directories
 * - PSR-11 container integration for dependency injection
 *
 * @template-use SupportsDiscovery<ValidatorInterface>
 */
final class ValidatorRegistry implements ValidatorRegistryInterface
{
    /** @use SupportsDiscovery<ValidatorInterface> */
    use SupportsDiscovery;

    /** @var array<array{validator: ValidatorInterface, priority: int}> */
    private array $validators = [];

    private bool $sorted = true;

    public function register(ValidatorInterface $validator, int $priority = 0): void
    {
        $this->validators[] = [
            'validator' => $validator,
            'priority' => $priority,
        ];

        $this->sorted = false;
    }

    public function get(PropertyMeta $property, mixed $value): ?ValidatorInterface
    {
        $this->resolvePendingClasses();
        $this->ensureSorted();

        foreach ($this->validators as $item) {
            if ($item['validator']->supports($property, $value)) {
                return $item['validator'];
            }
        }

        return null;
    }

    public function validate(PropertyMeta $property, mixed $value, ValidationContext $context): void
    {
        $this->resolvePendingClasses();
        $this->ensureSorted();

        $violations = [];

        foreach ($this->validators as $item) {
            if (! $item['validator']->supports($property, $value)) {
                continue;
            }

            try {
                $item['validator']->validate($property, $value, $context);
            } catch (ValidationException $e) {
                foreach ($e->getViolations() as $violation) {
                    $violations[] = $violation;
                }
            }
        }

        if ($violations !== []) {
            throw ValidationException::fromViolations(
                "Validation failed for property '{$property->name}'",
                $violations,
                $property->name,
            );
        }
    }

    public function canValidate(PropertyMeta $property, mixed $value): bool
    {
        return $this->get($property, $value) !== null;
    }

    protected function getItemInterface(): string
    {
        return ValidatorInterface::class;
    }

    protected function doRegister(object $instance, int $priority): void
    {
        /** @var ValidatorInterface $instance */
        $this->register($instance, $priority);
    }

    private function ensureSorted(): void
    {
        if ($this->sorted) {
            return;
        }

        usort(
            $this->validators,
            static fn (array $first, array $second): int => $second['priority'] <=> $first['priority'],
        );

        $this->sorted = true;
    }
}
