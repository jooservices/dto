<?php

declare(strict_types=1);

namespace JOOservices\Dto\Support;

use JsonException;
use ReflectionClass;

final class DtoFingerprint
{
    /**
     * @throws JsonException
     */
    public function hash(object $instance): string
    {
        $reflection = new ReflectionClass($instance);
        $state = [];

        foreach ($reflection->getProperties() as $property) {
            if (! $property->isPublic()) {
                continue;
            }

            $state[$property->getName()] = $property->getValue($instance);
        }

        return $this->hashState($state);
    }

    /**
     * @param  array<string, mixed>  $state
     *
     * @throws JsonException
     */
    public function hashState(array $state): string
    {
        /** @var array<string, mixed> $canonical */
        $canonical = $this->canonicalize($state);
        ksort($canonical);

        return hash('xxh3', json_encode($canonical, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<mixed>|mixed
     */
    private function canonicalize(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            $items = [];
            foreach ($value as $item) {
                $items[] = $this->canonicalize($item);
            }

            return $items;
        }

        $normalized = [];
        foreach ($value as $key => $item) {
            $normalized[(string) $key] = $this->canonicalize($item);
        }

        ksort($normalized);

        return $normalized;
    }
}
