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
use JOOservices\Dto\Meta\MetaFactoryInterface;
use ReflectionException;

final class EngineCopyWithApplier
{
    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
        private readonly HydratorInterface $hydrator,
        private readonly NestedValueCopier $nestedValueCopier = new NestedValueCopier(),
    ) {
    }

    /**
     * @param  array<string, mixed>  $patch
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function apply(object $instance, array $patch, Context $ctx): object
    {
        $meta = $this->metaFactory->create($instance::class);
        /** @var array<string, mixed> $state */
        $state = get_object_vars($instance);
        $state = $this->nestedValueCopier->copyUnpatched($state, $patch);

        foreach ($patch as $key => $value) {
            $propertyKey = $this->resolvePatchKey($key, $instance, $state, $value);
            $property = $meta->property($propertyKey);
            if ($property === null) {
                throw new MappingException(
                    message: 'Unknown property for with().',
                    path: $propertyKey,
                    expectedType: $instance::class,
                );
            }

            $state[$propertyKey] = $this->hydrator->castPatched($property, $value, $ctx);
        }

        return $this->hydrator->fromState($meta, $state, $ctx);
    }

    /**
     * @param  array<string, mixed>  $state
     *
     * @throws MappingException
     */
    private function resolvePatchKey(mixed $key, object $instance, array $state, mixed $value): string
    {
        if (! is_string($key)) {
            throw new MappingException(
                message: 'Unknown property for with().',
                path: is_scalar($key) ? (string) $key : '0',
                expectedType: $instance::class,
                givenValue: $value,
            );
        }

        if (! array_key_exists($key, $state)) {
            throw new MappingException(
                message: 'Unknown property for with().',
                path: $key,
                expectedType: $instance::class,
                givenValue: $value,
            );
        }

        return $key;
    }
}
