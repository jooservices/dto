<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;
use JOOservices\Dto\Collections\DataCollection;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Support\DtoFingerprint;
use JsonException;
use JsonSerializable;
use ReflectionException;

/**
 * Shared DTO/Data base: constructor-promoted contract, state-view copy helpers.
 */
abstract class AbstractDto extends CopiesViaConstructor implements JsonSerializable
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function beforeSerialization(array $data): array
    {
        return $data;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function toArray(?Context $ctx = null): array
    {
        $data = static::engine()->normalize($this, $ctx);

        return $this->beforeSerialization($data);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws ReflectionException
     */
    public function toJson(?Context $ctx = null, int $flags = 0): string
    {
        return static::engine()->normalizeToJson($this, $ctx, $flags);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    public function equals(self $other): bool
    {
        return $this->stateView() === $other->stateView();
    }

    /**
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public function diff(self $other): array
    {
        $left = $this->stateView();
        $right = $other->stateView();
        $diff = [];

        foreach ($left as $key => $value) {
            if (! array_key_exists($key, $right) || $right[$key] !== $value) {
                $diff[$key] = [
                    'old' => $value,
                    'new' => $right[$key] ?? null,
                ];
            }
        }

        foreach ($right as $key => $value) {
            if (! array_key_exists($key, $left)) {
                $diff[$key] = [
                    'old' => null,
                    'new' => $value,
                ];
            }
        }

        return $diff;
    }

    /**
     * @throws JsonException
     */
    public function hash(): string
    {
        return (new DtoFingerprint())->hash($this);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validate(?Context $ctx = null): void
    {
        static::engine()->validate($this, $ctx);
    }

    /**
     * @param  callable(static):mixed  $callback
     */
    public function when(bool $condition, callable $callback): mixed
    {
        return $condition ? $callback($this) : $this;
    }

    /**
     * @param  callable(static):mixed  $callback
     */
    public function unless(bool $condition, callable $callback): mixed
    {
        return $this->when(! $condition, $callback);
    }

    /**
     * @param  iterable<int|string, mixed>  $items
     * @return DataCollection<static>
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public static function collection(iterable $items, ?Context $ctx = null): DataCollection
    {
        /** @var class-string<static> $class */
        $class = static::class;

        return new DataCollection($class, $items, $ctx);
    }

    /**
     * @param  list<string>  $sourceKeys
     * @return PartialDtoBuilder<static>
     */
    public static function partial(array $sourceKeys): PartialDtoBuilder
    {
        /** @var class-string<static> $class */
        $class = static::class;

        return new PartialDtoBuilder($class, $sourceKeys);
    }
}
