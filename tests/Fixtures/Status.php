<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}
