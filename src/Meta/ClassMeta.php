<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Attributes\DiscriminatorMap;

/**
 * Cached reflection metadata for a DTO class.
 */
final readonly class ClassMeta
{
    /** @var array<string, PropertyMeta> */
    private array $propertyIndex;

    /**
     * @param  class-string  $className
     * @param  list<PropertyMeta>  $properties
     * @param  list<string>  $ctorParams
     */
    public function __construct(
        public string $className,
        public array $properties,
        public array $ctorParams,
        public bool $hasConstructor,
        public ?DiscriminatorMap $discriminator = null,
    ) {
        $index = [];
        foreach ($properties as $property) {
            $index[$property->name] = $property;
        }

        $this->propertyIndex = $index;
    }

    public function property(string $name): ?PropertyMeta
    {
        return $this->propertyIndex[$name] ?? null;
    }
}
