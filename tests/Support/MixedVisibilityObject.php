<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Support;

/**
 * Plain object with a mix of visibilities, used to prove ObjectInputNormalizer only extracts
 * public properties via get_object_vars() called from outside the class.
 */
final class MixedVisibilityObject
{
    public string $name = 'Ada';

    public int $age = 30;

    private string $secret = 'hidden';

    public function secret(): string
    {
        return $this->secret;
    }
}
