<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionException;

final class ClassFromNestedDtoFactory implements NestedDtoFactory
{
    public function __construct(
        private readonly SharedDefaults $defaults = new SharedDefaults(),
    ) {
    }

    /**
     * @param  class-string  $className
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function create(string $className, mixed $source, ?Context $ctx): object
    {
        if (! is_a($className, AbstractDto::class, true)) {
            throw new HydrationException(
                message: 'Nested type is not a DTO.',
                expectedType: $className,
            );
        }

        $next = $this->nextContext($ctx);

        if (is_array($source)) {
            /** @var array<string, mixed> $source */
            /** @var class-string<AbstractDto> $className */
            return $className::from($source, $next);
        }

        if (! is_object($source) && ! is_string($source)) {
            throw new HydrationException(
                message: 'Nested DTO source must be array, object, or JSON string.',
                expectedType: $className,
                givenType: get_debug_type($source),
            );
        }

        /** @var class-string<AbstractDto> $className */
        return $className::from($source, $next);
    }

    /**
     * @throws InvalidArgumentException
     */
    private function nextContext(?Context $ctx): Context
    {
        $depthRaw = $ctx?->customData['hydrationDepth'] ?? 0;
        $depth = is_int($depthRaw) ? $depthRaw : 0;
        $base = $ctx ?? $this->defaults->looseContext();

        return $base->withCustomData(['hydrationDepth' => $depth + 1]);
    }
}
