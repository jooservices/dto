<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

/** Ten-field DTO used by performance benchmarks. */
final class BenchmarkTenFieldDto extends Dto
{
    public function __construct(
        public readonly string $field01,
        public readonly string $field02,
        public readonly string $field03,
        public readonly int $field04,
        public readonly int $field05,
        public readonly bool $field06,
        public readonly ?string $field07 = null,
        public readonly ?int $field08 = null,
        public readonly string $field09 = 'default',
        public readonly int $field10 = 0,
    ) {
    }
}
