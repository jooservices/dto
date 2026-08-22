<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Meta\MetaCacheInterface;

final class EngineFactory
{
    private ?MetaCacheInterface $metaCache = null;

    public function withMetaCache(MetaCacheInterface $cache): self
    {
        $clone = clone $this;
        $clone->metaCache = $cache;

        return $clone;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function create(): Engine
    {
        return (new EngineAssembly())->build($this->metaCache);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function createDefault(): Engine
    {
        return (new self())->create();
    }
}
