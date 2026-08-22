<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;

/**
 * Cached immutable defaults shared across hydration and normalization.
 */
final class SharedDefaults
{
    private ?Context $looseContext = null;

    private ?Context $validationContext = null;

    /**
     * @throws InvalidArgumentException
     */
    public function looseContext(): Context
    {
        if ($this->looseContext === null) {
            $this->looseContext = new Context(validationEnabled: false, sourceKeyOut: false);
        }

        return $this->looseContext;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validationContext(): Context
    {
        if ($this->validationContext === null) {
            $this->validationContext = new Context(validationEnabled: true, sourceKeyOut: false);
        }

        return $this->validationContext;
    }
}
