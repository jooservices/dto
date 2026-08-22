<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JsonSerializable;

final class NormalizerValueTransformer
{
    public function __construct(
        private readonly TransformerRegistryInterface $transformers,
        private readonly NormalizerTransformApplier $applier = new NormalizerTransformApplier(),
    ) {
    }

    public function registry(): TransformerRegistryInterface
    {
        return $this->transformers;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function transformPropertyValue(mixed $value, PropertyMeta $property): mixed
    {
        return $this->applier->apply($value, $property, $this->transformers);
    }

    /**
     * @throws HydrationException
     */
    public function assertFiniteFloat(mixed $value, PropertyMeta $property): void
    {
        if (is_float($value) && ! is_finite($value)) {
            throw new HydrationException(
                message: 'INF and NaN cannot be serialized to JSON.',
                path: $property->name,
                givenValue: $property->redactWith ?? $value,
            );
        }
    }

    /**
     * @param  array<string, mixed>  $value
     * @param  callable(mixed, PropertyMeta): mixed  $normalizeNested
     * @return array<int|string, mixed>
     */
    public function normalizeArrayItems(
        array $value,
        PropertyMeta $property,
        callable $normalizeNested,
    ): array {
        $items = [];
        foreach ($value as $offset => $item) {
            $transformed = $this->transformers->transform($item);
            if (is_object($transformed)) {
                $items[$offset] = $normalizeNested($transformed, $property);
                continue;
            }
            $items[$offset] = $transformed;
        }

        return $items;
    }

    public function jsonSerializeIfNeeded(mixed $value): mixed
    {
        if ($value instanceof JsonSerializable) {
            return $value->jsonSerialize();
        }

        return $value;
    }

    /**
     * A nested value whose class our own meta system cannot describe (not
     * constructor-promoted the way a DTO must be, or no constructor at all).
     *
     * Our own `AbstractDto`/`Data` instances are returned untouched: calling
     * their `jsonSerialize()` here would just re-enter the same failing meta
     * build. A genuine third-party `JsonSerializable` value object still gets
     * resolved, so `toArray()` and `toJson()` agree on its shape.
     */
    public function fallbackForUndescribableNested(object $value): mixed
    {
        if ($value instanceof AbstractDto) {
            return $value;
        }

        $serialized = $this->jsonSerializeIfNeeded($value);
        if ($serialized !== $value) {
            return $serialized;
        }

        return '[object ' . $value::class . ']';
    }
}
