<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Dto;

final class LazyUserDto extends Dto implements ComputesLazyProperties
{
    public function __construct(
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $email,
        public readonly int $age,
    ) {}

    public function computeLazyProperties(): array
    {
        return [
            // Immediate value
            'fullName' => $this->firstName.' '.$this->lastName,

            // Closure for truly lazy evaluation
            'initials' => fn () => strtoupper($this->firstName[0].$this->lastName[0]),

            // Complex lazy computation
            'stats' => fn () => [
                'nameLength' => strlen($this->firstName.$this->lastName),
                'emailDomain' => explode('@', $this->email)[1] ?? '',
                'isAdult' => $this->age >= 18,
            ],

            // Simple derived value
            'displayEmail' => substr($this->email, 0, 3).'***@'.(explode('@', $this->email)[1] ?? ''),
        ];
    }
}
