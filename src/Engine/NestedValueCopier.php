<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use DateTimeImmutable;
use DateTimeInterface;
use InvalidArgumentException;
use JOOservices\Dto\Core\AbstractDto;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use ReflectionException;

final class NestedValueCopier
{
    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $patch
     * @return array<string, mixed>
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function copyUnpatched(array $state, array $patch): array
    {
        $copied = [];
        foreach ($state as $key => $value) {
            $copied[$key] = array_key_exists($key, $patch)
                ? $value
                : $this->copy($value);
        }

        return $copied;
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function copy(mixed $value): mixed
    {
        if ($value instanceof AbstractDto) {
            return $value->clone();
        }

        if ($value instanceof DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof DateTimeInterface) {
            return clone $value;
        }

        if (! is_array($value)) {
            return $value;
        }

        $copied = [];
        foreach ($value as $key => $item) {
            $copied[$key] = $this->copy($item);
        }

        return $copied;
    }
}
