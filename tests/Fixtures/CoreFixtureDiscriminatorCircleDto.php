<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Fixtures;

use JOOservices\Dto\Attributes\MapFrom;

final class CoreFixtureDiscriminatorCircleDto extends CoreFixtureDiscriminatorShapeDto
{
    /**
     * Each DTO subtype in this polymorphic hierarchy declares its own full,
     * independently-promoted constructor (the library's constructor contract),
     * not an extension of the parent's — so there is nothing to forward.
     *
     * @phpstan-ignore constructor.missingParentCall
     */
    public function __construct(
        #[MapFrom('type')]
        public readonly string $kind,
        public readonly string $label = 'shape',
        public readonly float $radius = 1.0,
    ) {
    }
}
