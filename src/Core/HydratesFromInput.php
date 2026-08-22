<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;
use JOOservices\Dto\Engine\EngineHolder;
use JOOservices\Dto\Engine\EngineInterface;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JsonException;
use ReflectionException;

/**
 * Static hydration entry points shared by DTO and Data.
 */
abstract class HydratesFromInput
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function transformInput(array $data): array
    {
        return $data;
    }

    protected function afterHydration(): void
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    protected static function engine(): EngineInterface
    {
        return (new EngineHolder())->current();
    }

    /**
     * @param  array<string, mixed>|object|string  $source
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function from(mixed $source, ?Context $ctx = null): static
    {
        if (is_array($source)) {
            $source = static::transformInput($source);
        }

        /** @var static $instance */
        $instance = static::engine()->hydrate(static::class, $source, $ctx);
        $instance->afterHydration();

        return $instance;
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function fromArray(array $data, ?Context $ctx = null): static
    {
        return static::from($data, $ctx);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function fromJson(string $json, ?Context $ctx = null): static
    {
        return static::from($json, $ctx);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function fromObject(object $object, ?Context $ctx = null): static
    {
        return static::from($object, $ctx);
    }

    public static function tryFrom(mixed $source, ?Context $ctx = null): ?static
    {
        if (! is_array($source) && ! is_object($source) && ! is_string($source)) {
            return null;
        }

        try {
            if (is_string($source) || is_object($source)) {
                return static::from($source, $ctx);
            }

            /** @var array<string, mixed> $source */
            return static::from($source, $ctx);
        } catch (
            HydrationException
            | MappingException
            | InvalidArgumentException
            | ReflectionException
            | CastException
            | ValidationException
        ) {
            return null;
        }
    }

    /**
     * PSR-7 duck-typed: parsed body merged over query params.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function fromRequest(object $request, ?Context $ctx = null): static
    {
        if (! method_exists($request, 'getParsedBody') || ! method_exists($request, 'getQueryParams')) {
            throw new HydrationException(
                message: 'fromRequest() expects a PSR-7 server request (getParsedBody/getQueryParams).',
                givenType: $request::class,
            );
        }

        $body = $request->getParsedBody();
        $query = $request->getQueryParams();
        /** @var array<string, mixed> $merged */
        $merged = [];
        if (is_array($query)) {
            /** @var array<string, mixed> $query */
            $merged = $query;
        }

        if (is_array($body)) {
            /** @var array<string, mixed> $body */
            $merged = array_merge($merged, $body);
        } elseif (is_object($body)) {
            /** @var array<string, mixed> $bodyArray */
            $bodyArray = json_decode(
                json_encode($body, JSON_THROW_ON_ERROR),
                true,
                32,
                JSON_THROW_ON_ERROR,
            );
            $merged = array_merge($merged, $bodyArray);
        }

        return static::from($merged, $ctx);
    }
}
