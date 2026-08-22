<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Core\Dto;

/**
 * Base of a polymorphic hierarchy where the discriminator field ("kind") is only
 * ever supplied under a differently-named source key ("type") via MapFrom. This
 * proves discriminator resolution reads the *mapped* value, not the raw source key.
 */
#[DiscriminatorMap(field: 'kind', map: [
    'circle' => CoreFixtureDiscriminatorCircleDto::class,
])]
class CoreFixtureDiscriminatorShapeDto extends Dto
{
    public function __construct(
        #[MapFrom('type')]
        public readonly string $kind,
        public readonly string $label = 'shape',
    ) {
    }
}
