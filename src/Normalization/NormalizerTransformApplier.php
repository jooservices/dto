<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Support\CachedOptionedInstantiator;

final class NormalizerTransformApplier
{
    public function __construct(
        private readonly CachedOptionedInstantiator $instantiator = new CachedOptionedInstantiator(),
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function apply(mixed $value, PropertyMeta $property, TransformerRegistryInterface $transformers): mixed
    {
        foreach ($property->resolved->transformWith as $attribute) {
            $transformer = $this->instantiator->make($attribute->transformerClass, $attribute->options);
            if (! $transformer instanceof TransformerInterface) {
                throw new InvalidArgumentException(
                    'TransformWith class must implement TransformerInterface.',
                );
            }

            $value = $transformer->transform($value);
        }

        return $transformers->transform($value);
    }
}
