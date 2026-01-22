<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration\Naming;

interface NamingStrategyInterface
{
    public const string DIRECTION_TO_SOURCE = 'toSource';

    public const string DIRECTION_TO_PROPERTY = 'toProperty';

    public function convert(string $name, string $direction): string;
}
