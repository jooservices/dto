<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Core\Dto;

final class CoreFixtureTransformInputDto extends Dto
{
    public function __construct(
        public readonly string $name,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function transformInput(array $data): array
    {
        $name = $data['name'] ?? null;
        if (is_string($name)) {
            $data['name'] = strtoupper($name);
        }

        return $data;
    }
}
