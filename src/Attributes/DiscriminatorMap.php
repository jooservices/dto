<?php

declare(strict_types=1);

namespace JOOservices\Dto\Attributes;

use Attribute;
use Closure;

/**
 * Map discriminator values to concrete DTO classes for polymorphic properties.
 *
 * Supports both field-based and callable discriminators for complex logic.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final readonly class DiscriminatorMap
{
    /**
     * @param  string|Closure  $discriminator  Field name or Closure(array $data): string
     * @param  array<string, class-string>  $map  Mapping from discriminator value to class name
     */
    public function __construct(
        public string|Closure $discriminator,
        public array $map,
    ) {}

    /**
     * Resolve the target class based on input data.
     *
     * @param  array<string, mixed>  $data
     * @return class-string|null
     */
    public function resolveType(array $data): ?string
    {
        if ($this->discriminator instanceof Closure) {
            $discriminatorValue = ($this->discriminator)($data);
        } else {
            $discriminatorValue = $data[$this->discriminator] ?? null;
        }

        if ($discriminatorValue === null) {
            return null;
        }

        return $this->map[$discriminatorValue] ?? null;
    }
}
