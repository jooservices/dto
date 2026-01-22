<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\HydratorInterface;
use JOOservices\Dto\Hydration\InputNormalizerInterface;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Normalization\NormalizerInterface;
use JsonException;
use ReflectionMethod;

use const JSON_THROW_ON_ERROR;

class Engine implements EngineInterface
{
    /** @var array<InputNormalizerInterface> */
    private array $inputNormalizers = [];

    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
        private readonly HydratorInterface $hydrator,
        private readonly NormalizerInterface $normalizer,
    ) {}

    public function addInputNormalizer(InputNormalizerInterface $normalizer): self
    {
        $this->inputNormalizers[] = $normalizer;

        return $this;
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     */
    public function hydrate(string $class, mixed $source, ?Context $ctx = null): object
    {
        $data = $this->normalizeInput($source);

        // Lifecycle hook: transformInput (static method)
        $data = $this->callTransformInput($class, $data);

        $meta = $this->metaFactory->create($class);

        /** @var T $instance */
        $instance = $this->hydrator->hydrate($meta, $data, $ctx);

        // Lifecycle hook: afterHydration (instance method)
        $this->callAfterHydration($instance);

        return $instance;
    }

    public function normalize(object $instance, ?Context $ctx = null): array
    {
        // Lifecycle hook: beforeSerialization (instance method)
        $this->callBeforeSerialization($instance);

        $meta = $this->metaFactory->create($instance::class);

        return $this->normalizer->normalize($instance, $meta, $ctx);
    }

    public function normalizeToJson(object $instance, ?Context $ctx = null, int $flags = 0): string
    {
        $array = $this->normalize($instance, $ctx);

        try {
            return json_encode($array, $flags | JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new HydrationException(
                message: 'Failed to encode to JSON: '.$e->getMessage(),
                previous: $e,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeInput(mixed $source): array
    {
        if (is_array($source)) {
            /** @var array<string, mixed> $source */
            return $source;
        }

        foreach ($this->inputNormalizers as $normalizer) {
            if ($normalizer->supports($source)) {
                /** @var array<string, mixed> */
                return $normalizer->normalize($source);
            }
        }

        throw new HydrationException(
            message: 'Cannot normalize input of type '.get_debug_type($source),
        );
    }

    /**
     * Call the static transformInput lifecycle hook if it exists.
     *
     * @param  class-string  $class
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function callTransformInput(string $class, array $data): array
    {
        if (! method_exists($class, 'transformInput')) {
            return $data;
        }

        $reflection = new ReflectionMethod($class, 'transformInput');

        /** @var array<string, mixed> */
        return $reflection->invoke(null, $data);
    }

    /**
     * Call the afterHydration lifecycle hook if it exists.
     */
    private function callAfterHydration(object $instance): void
    {
        if (! method_exists($instance, 'afterHydration')) {
            return;
        }

        $reflection = new ReflectionMethod($instance, 'afterHydration');
        $reflection->invoke($instance);
    }

    /**
     * Call the beforeSerialization lifecycle hook if it exists.
     */
    private function callBeforeSerialization(object $instance): void
    {
        if (! method_exists($instance, 'beforeSerialization')) {
            return;
        }

        $reflection = new ReflectionMethod($instance, 'beforeSerialization');
        $reflection->invoke($instance);
    }
}
