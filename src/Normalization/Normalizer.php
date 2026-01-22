<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use Closure;
use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Meta\PropertyMeta;
use LogicException;
use SplObjectStorage;

class Normalizer implements NormalizerInterface
{
    /**
     * Cache for lazy property computations during a normalization run.
     * Prevents recomputing the same lazy properties multiple times.
     *
     * @var SplObjectStorage<object, array<string, mixed>>|null
     */
    private ?SplObjectStorage $lazyCache = null;

    public function __construct(
        private readonly TransformerRegistryInterface $transformerRegistry,
        private readonly MetaFactoryInterface $metaFactory,
    ) {}

    public function normalize(object $instance, ClassMeta $meta, ?Context $ctx, int $depth = 0): array
    {
        $options = $ctx?->getSerializationOptions() ?? new SerializationOptions;

        if (! $options->canDescend($depth)) {
            return [];
        }

        $result = [];

        foreach ($meta->getVisibleProperties() as $property) {
            if (! $options->shouldInclude($property->name)) {
                continue;
            }

            $value = $this->getPropertyValue($instance, $property);
            $normalizedValue = $this->normalizeValue($value, $property, $ctx, $depth);
            $result[$property->name] = $normalizedValue;
        }

        // Handle lazy properties if the instance implements ComputesLazyProperties
        if ($instance instanceof ComputesLazyProperties) {
            // Initialize cache for this normalization run only at depth 0
            if ($depth === 0) {
                $this->lazyCache = new SplObjectStorage;
            }

            $result = $this->mergeLazyProperties($instance, $result, $options, $ctx, $depth);

            // Clear cache after top-level normalization completes
            if ($depth === 0) {
                $this->lazyCache = null;
            }
        }

        return $result;
    }

    private function getPropertyValue(object $instance, PropertyMeta $property): mixed
    {
        /** @var array<string, mixed> $vars */
        $vars = get_object_vars($instance);

        return $vars[$property->name] ?? null;
    }

    private function normalizeValue(mixed $value, PropertyMeta $property, ?Context $ctx, int $depth): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Dto) {
            return $this->normalizeDto($value, $ctx, $depth + 1);
        }

        if (is_array($value)) {
            return $this->normalizeArray($value, $property, $ctx, $depth);
        }

        // Priority 1: Use attribute-specified transformer (#[TransformWith])
        if ($property->transformerClass !== null) {
            return $this->transformWithAttribute($property, $value, $ctx);
        }

        // Priority 2: Use registry-based transformer
        if ($this->transformerRegistry->canTransform($property, $value)) {
            return $this->transformerRegistry->transform($property, $value, $ctx);
        }

        if (is_object($value)) {
            return $this->normalizeObject($value, $ctx, $depth + 1);
        }

        return $value;
    }

    /**
     * Transform using the transformer class specified via #[TransformWith] attribute.
     */
    private function transformWithAttribute(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        /** @var class-string<TransformerInterface> $transformerClass */
        $transformerClass = $property->transformerClass;

        /** @var TransformerInterface $transformer */
        $transformer = new $transformerClass;

        return $transformer->transform($property, $value, $ctx);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeDto(Dto $dto, ?Context $ctx, int $depth): array
    {
        $meta = $this->metaFactory->create($dto::class);

        return $this->normalize($dto, $meta, $ctx, $depth);
    }

    /**
     * @param  array<mixed>  $values
     * @return array<mixed>
     */
    private function normalizeArray(array $values, PropertyMeta $property, ?Context $ctx, int $depth): array
    {
        $result = [];

        foreach ($values as $key => $value) {
            if ($value instanceof Dto) {
                $result[$key] = $this->normalizeDto($value, $ctx, $depth + 1);
            } elseif (is_array($value)) {
                $result[$key] = $this->normalizeArray($value, $property, $ctx, $depth);
            } elseif ($this->transformerRegistry->canTransform($property, $value)) {
                $result[$key] = $this->transformerRegistry->transform($property, $value, $ctx);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeObject(object $object, ?Context $ctx, int $depth): array
    {
        $options = $ctx?->getSerializationOptions() ?? new SerializationOptions;

        if (! $options->canDescend($depth)) {
            return [];
        }

        return get_object_vars($object);
    }

    /**
     * Merge lazy properties into the result array.
     *
     * Implements all 4 critical fixes:
     * - Fix B: Closure-based lazy evaluation (only compute when needed)
     * - Fix C: Collision detection (throw if lazy conflicts with property)
     * - Fix D: Deep recursive normalization
     * - Fix F: Cache per normalization run
     *
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function mergeLazyProperties(
        ComputesLazyProperties $instance,
        array $result,
        SerializationOptions $options,
        ?Context $ctx,
        int $depth,
    ): array {
        // Get or compute lazy properties (cached per instance within this normalization run)
        if ($this->lazyCache !== null && ! $this->lazyCache->offsetExists($instance)) {
            $this->lazyCache[$instance] = $instance->computeLazyProperties();
        }

        $lazyProperties = $this->lazyCache !== null && $this->lazyCache->offsetExists($instance)
            ? $this->lazyCache[$instance]
            : $instance->computeLazyProperties();

        foreach ($lazyProperties as $name => $value) {
            // Fix B: Check filter BEFORE computing (if Closure)
            if (! $options->shouldIncludeLazy($name)) {
                continue; // Skip without computing!
            }

            // Respect only/except filters
            if (! $options->shouldInclude($name)) {
                continue;
            }

            // Fix C: Collision detection - lazy cannot override existing properties
            if (isset($result[$name]) || array_key_exists($name, $result)) {
                throw new LogicException(
                    "Lazy property '{$name}' conflicts with existing property in ".$instance::class,
                );
            }

            // Fix B: Compute Closure only now (truly lazy)
            $computedValue = $value instanceof Closure ? $value() : $value;

            // Fix D: Deep recursive normalization
            $result[$name] = $this->normalizeMixed($computedValue, $ctx, $depth);
        }

        return $result;
    }

    /**
     * Deep recursive normalization for mixed values.
     *
     * Fix D: Handles nested arrays, DTOs, and objects at any depth.
     *
     * @return mixed Normalized value
     */
    private function normalizeMixed(mixed $value, ?Context $ctx, int $depth): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Dto) {
            return $this->normalizeDto($value, $ctx, $depth + 1);
        }

        if (is_array($value)) {
            $result = [];

            foreach ($value as $key => $item) {
                $result[$key] = $this->normalizeMixed($item, $ctx, $depth);
            }

            return $result;
        }

        if (is_object($value)) {
            return $this->normalizeObject($value, $ctx, $depth + 1);
        }

        // Scalar or other types
        return $value;
    }
}
