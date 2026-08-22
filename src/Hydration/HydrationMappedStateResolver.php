<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

final class HydrationMappedStateResolver
{
    public function __construct(
        private readonly Mapper $mapper,
        private readonly DiscriminatorResolver $discriminators,
        private readonly UnknownKeyGuard $unknownKeys,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{meta: ClassMeta, mapped: array<string, mixed>}
     *
     * @throws \JOOservices\Dto\Exceptions\HydrationException
     * @throws \JOOservices\Dto\Exceptions\MappingException
     * @throws ReflectionException
     */
    public function resolve(ClassMeta $meta, array $data, Context $ctx): array
    {
        $mapped = $this->mapper->map($data, $meta, $ctx);
        $resolved = $this->discriminators->resolve($meta, $mapped, $ctx);
        if ($resolved->className !== $meta->className) {
            $meta = $resolved;
            $mapped = $this->mapper->map($data, $meta, $ctx);
        }

        $this->unknownKeys->assertKnown($data, $meta, $ctx);

        return ['meta' => $meta, 'mapped' => $mapped];
    }
}
