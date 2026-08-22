<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;
use ReflectionException;

final class Normalizer implements NormalizerInterface
{
    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
        private readonly NormalizerPropertySupport $propertySupport,
        private readonly NormalizerValueTransformer $valueTransformer,
        private readonly Context $defaultContext,
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function normalize(object $instance, ?Context $ctx): array
    {
        $ctx ??= $this->defaultContext;
        $depth = $this->currentDepth($ctx);

        return $this->normalizeAtDepth($instance, $ctx, $depth);
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function normalizeAtDepth(object $instance, Context $ctx, int $depth): array
    {
        $options = $ctx->serializationOptionsOrDefault();
        if (! $options->canDescend($depth)) {
            return [];
        }

        $meta = $this->metaFactory->create($instance::class);
        $out = $this->collectProperties($instance, $meta, $ctx, $options, $depth);
        $out = $this->propertySupport->mergeLazy($instance, $out, $options, $this->valueTransformer->registry());
        $out = $this->propertySupport->emitDiscriminator($meta, $out);

        if ($options->wrap !== null && $depth === 0) {
            return [$options->wrap => $out];
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function collectProperties(
        object $instance,
        ClassMeta $meta,
        Context $ctx,
        SerializationOptions $options,
        int $depth,
    ): array {
        $vars = get_object_vars($instance);
        $out = [];

        foreach ($meta->properties as $property) {
            if ($this->propertySupport->isHidden($property)) {
                continue;
            }

            if (! $options->shouldInclude($property->name)) {
                continue;
            }

            if (! array_key_exists($property->name, $vars)) {
                continue;
            }

            if ($property->redactWith !== null) {
                $out[$this->propertySupport->outputKey($property, $ctx)] = $property->redactWith;

                continue;
            }

            $value = $this->normalizeValue($vars[$property->name], $property, $ctx, $options, $depth);
            $out[$this->propertySupport->outputKey($property, $ctx)] = $value;
        }

        return $out;
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function normalizeValue(
        mixed $value,
        PropertyMeta $property,
        Context $ctx,
        SerializationOptions $options,
        int $depth,
    ): mixed {
        $value = $this->valueTransformer->transformPropertyValue($value, $property);

        if (is_object($value)) {
            return $this->normalizeNestedObject($value, $ctx, $depth);
        }

        if (is_array($value)) {
            /** @var array<string, mixed> $value */
            return $this->valueTransformer->normalizeArrayItems(
                $value,
                $property,
                fn(mixed $item, PropertyMeta $prop): mixed => $this->normalizeValue(
                    $item,
                    $prop,
                    $ctx,
                    $options,
                    $depth,
                ),
            );
        }

        $this->valueTransformer->assertFiniteFloat($value, $property);

        return $value;
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    private function normalizeNestedObject(object $value, Context $ctx, int $depth): mixed
    {
        try {
            $nestedMeta = $this->metaFactory->create($value::class);
        } catch (HydrationException) {
            return $this->valueTransformer->fallbackForUndescribableNested($value);
        }

        if (! $nestedMeta->hasConstructor) {
            return $this->valueTransformer->fallbackForUndescribableNested($value);
        }

        return $this->normalizeAtDepth($value, $ctx, $depth + 1);
    }

    private function currentDepth(Context $ctx): int
    {
        $rawDepth = $ctx->customData['normalizeDepth'] ?? 0;

        return is_int($rawDepth) ? $rawDepth : 0;
    }
}
