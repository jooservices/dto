<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Casting\CasterRegistryInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

final class ArrayOfCaster implements CasterInterface
{
    public function __construct(
        private readonly CasterRegistryInterface $registry,
    ) {}

    public function supports(PropertyMeta $property, mixed $value): bool
    {
        if (! $property->type->isTypedArray()) {
            return false;
        }

        return is_array($value);
    }

    /**
     * @return array<mixed>
     */
    public function cast(PropertyMeta $property, mixed $value, ?Context $ctx): array
    {
        if (! is_array($value)) {
            throw CastException::cannotCast($value, 'array', $property->name);
        }

        $itemType = $property->type->arrayItemType;

        if ($itemType === null) {
            return $value;
        }

        $result = [];

        foreach ($value as $key => $item) {
            $result[$key] = $this->castItem($item, $itemType, $property, $ctx, $key);
        }

        return $result;
    }

    private function castItem(
        mixed $item,
        TypeDescriptor $itemType,
        PropertyMeta $property,
        ?Context $ctx,
        int|string $key,
    ): mixed {
        $itemPropertyMeta = new PropertyMeta(
            name: $property->name."[{$key}]",
            type: $itemType,
            isReadonly: $property->isReadonly,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );

        if (! $this->registry->canCast($itemPropertyMeta, $item)) {
            return $item;
        }

        return $this->registry->cast($itemPropertyMeta, $item, $ctx);
    }
}
