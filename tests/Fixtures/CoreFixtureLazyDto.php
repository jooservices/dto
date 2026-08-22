<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use Closure;
use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Dto;

final class CoreFixtureLazyDto extends Dto implements ComputesLazyProperties
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    /**
     * @return array<string, Closure():mixed|mixed>
     */
    public function computeLazyProperties(): array
    {
        return [
            'upper' => fn(): string => strtoupper($this->name),
            'eager' => 'static-value',
        ];
    }
}
