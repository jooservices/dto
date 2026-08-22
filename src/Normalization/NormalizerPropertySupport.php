<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use Closure;
use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;

final class NormalizerPropertySupport
{
    /**
     * @param  array<string, mixed>  $out
     * @return array<string, mixed>
     */
    public function mergeLazy(
        object $instance,
        array $out,
        SerializationOptions $options,
        TransformerRegistryInterface $transformers,
    ): array {
        if ($options->includeLazy === null || ! $instance instanceof ComputesLazyProperties) {
            return $out;
        }

        foreach ($instance->computeLazyProperties() as $name => $lazy) {
            if (! $options->shouldIncludeLazy($name)) {
                continue;
            }

            $value = $lazy instanceof Closure ? $lazy() : $lazy;
            $out[$name] = $transformers->transform($value);
        }

        return $out;
    }

    public function isHidden(PropertyMeta $property): bool
    {
        return $property->hidden === PropertyMeta::HIDDEN;
    }

    public function outputKey(PropertyMeta $property, Context $ctx): string
    {
        if ($property->resolved->mapToKey !== null) {
            return $property->resolved->mapToKey;
        }

        if ($ctx->sourceKeyOut && $ctx->namingStrategy !== null) {
            return $ctx->namingStrategy->toSource($property->name);
        }

        if ($property->resolved->mapFromKey !== null && $ctx->sourceKeyOut) {
            return $property->resolved->mapFromKey;
        }

        return $property->name;
    }

    /**
     * @param  array<string, mixed>  $out
     * @return array<string, mixed>
     */
    public function emitDiscriminator(ClassMeta $meta, array $out): array
    {
        $discriminator = $meta->discriminator;
        if ($discriminator === null) {
            return $out;
        }

        foreach ($discriminator->map as $value => $className) {
            if ($className === $meta->className) {
                $out[$discriminator->field] = $value;

                return $out;
            }
        }

        return $out;
    }
}
