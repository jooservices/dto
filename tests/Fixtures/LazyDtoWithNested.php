<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Dto;

final class LazyDtoWithNested extends Dto implements ComputesLazyProperties
{
    public function __construct(
        public readonly string $name,
        public readonly AddressDto $address,
    ) {}

    public function computeLazyProperties(): array
    {
        return [
            // Lazy property that returns a DTO
            'addressSummary' => fn () => $this->address,

            // Lazy property with nested array of data
            'nestedData' => fn () => [
                'level1' => [
                    'level2' => [
                        'address' => $this->address,
                        'name' => $this->name,
                    ],
                ],
            ],
        ];
    }
}
