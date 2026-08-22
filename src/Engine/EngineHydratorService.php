<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\HydratorInterface;
use JOOservices\Dto\Hydration\InputNormalizerInterface;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use ReflectionException;

final class EngineHydratorService
{
    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
        private readonly HydratorInterface $hydrator,
        private readonly InputNormalizerInterface $inputNormalizer,
    ) {
    }

    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function hydrate(string $class, mixed $source, Context $ctx): object
    {
        $data = $this->inputNormalizer->normalize($source);
        $meta = $this->metaFactory->create($class);
        $instance = $this->hydrator->hydrate($meta, $data, $ctx);

        /** @var T $instance */
        return $instance;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     */
    public function normalizeInput(mixed $source): array
    {
        return $this->inputNormalizer->normalize($source);
    }
}
