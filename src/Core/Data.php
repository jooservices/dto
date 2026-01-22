<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use JOOservices\Dto\Core\Traits\MutatesData;

abstract class Data extends Dto
{
    use MutatesData;
}
