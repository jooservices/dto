<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Core\Context;

interface EngineInterface
{
    /**
     * @template T of object
     *
     * @param  class-string<T>  $class
     * @return T
     */
    public function hydrate(string $class, mixed $source, ?Context $ctx = null): object;

    /**
     * @return array<string, mixed>
     */
    public function normalize(object $instance, ?Context $ctx = null): array;

    public function normalizeToJson(object $instance, ?Context $ctx = null, int $flags = 0): string;
}
