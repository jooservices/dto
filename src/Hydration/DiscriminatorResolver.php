<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use ReflectionException;

final class DiscriminatorResolver
{
    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
    ) {
    }

    /**
     * @param  array<string, mixed>  $mapped
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function resolve(ClassMeta $meta, array $mapped, ?Context $ctx): ClassMeta
    {
        unset($ctx);
        $discriminator = $meta->discriminator;
        if ($discriminator === null) {
            return $meta;
        }

        if (! array_key_exists($discriminator->field, $mapped)) {
            throw new HydrationException(
                message: 'Missing discriminator field.',
                path: $discriminator->field,
                expectedType: $meta->className,
            );
        }

        $raw = $mapped[$discriminator->field];
        $key = is_scalar($raw) ? (string) $raw : null;
        if ($key === null || ! array_key_exists($key, $discriminator->map)) {
            throw new HydrationException(
                message: 'Unknown discriminator value.',
                path: $discriminator->field,
                expectedType: $meta->className,
                givenValue: $raw,
            );
        }

        $target = $discriminator->map[$key];
        if ($target === $meta->className) {
            return $meta;
        }

        if (! is_subclass_of($target, $meta->className) || ! is_subclass_of($target, AbstractDto::class)) {
            throw new HydrationException(
                message: 'Discriminator target is not a DTO subclass.',
                path: $discriminator->field,
                expectedType: $meta->className,
                givenType: $target,
            );
        }

        return $this->metaFactory->create($target);
    }
}
