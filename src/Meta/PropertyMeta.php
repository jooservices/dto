<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Hydration\Naming\NamingStrategyInterface;

/**
 * Per-property metadata snapshot.
 */
final readonly class PropertyMeta
{
    public const int VISIBLE = 0;

    public const int HIDDEN = 1;

    /**
     * @param  list<object>  $attributes
     */
    public function __construct(
        public string $name,
        public TypeDescriptor $type,
        public bool $allowsNull,
        public bool $hasDefault,
        public mixed $defaultValue = null,
        public int $hidden = 0,
        public array $attributes = [],
        public ?string $redactWith = null,
        public PropertyMetaResolvedAttributes $resolved = new PropertyMetaResolvedAttributes(),
    ) {
    }

    /**
     * @return list<string>
     */
    public function sourceKeysFor(?NamingStrategyInterface $strategy): array
    {
        if ($strategy === null) {
            return $this->resolved->sourceKeys;
        }

        $keys = $this->resolved->sourceKeys;
        $keys[] = $strategy->toSource($this->name);

        return array_values(array_unique($keys));
    }
}
