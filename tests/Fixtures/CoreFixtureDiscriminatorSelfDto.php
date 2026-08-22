<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Core\Dto;

/**
 * `kind` is Hidden so it never appears via normal property collection; the only
 * way it can surface in normalize() output is via discriminator emission.
 */
#[DiscriminatorMap(field: 'kind', map: [
    'self' => CoreFixtureDiscriminatorSelfDto::class,
])]
final class CoreFixtureDiscriminatorSelfDto extends Dto
{
    public function __construct(
        #[Hidden]
        public readonly string $kind,
        public readonly string $label = 'x',
    ) {
    }
}
