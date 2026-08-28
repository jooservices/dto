<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CoreFixtureBeforeSerializationDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function beforeSerialization(array $data): array
    {
        $name = $data['name'] ?? '';
        $data['name'] = is_string($name) ? strtoupper($name) : $name;

        return $data;
    }
}
