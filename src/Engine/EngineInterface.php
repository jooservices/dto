<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JsonException;
use ReflectionException;

interface EngineInterface
{
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
    public function hydrate(string $class, mixed $source, ?Context $ctx = null): object;

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function normalize(object $instance, ?Context $ctx = null): array;

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function normalizeToJson(object $instance, ?Context $ctx = null, int $flags = 0): string;

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     */
    public function normalizeInput(mixed $source): array;

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validate(object $instance, ?Context $ctx = null): void;

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
    public function copyWith(object $instance, array $patch, ?Context $ctx): object;
}
