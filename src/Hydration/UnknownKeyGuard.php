<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Meta\ClassMeta;

final class UnknownKeyGuard
{
    public function __construct(
        private readonly Mapper $mapper,
    ) {
    }

    /**
     * @param  array<string, mixed>  $source
     *
     * @throws MappingException
     */
    public function assertKnown(array $source, ClassMeta $meta, Context $ctx): void
    {
        if (! $ctx->shouldRejectUnknownKeys()) {
            return;
        }

        $disc = $meta->discriminator?->field;
        $unknown = $this->mapper->unknownKeys($source, $meta, $ctx, $disc);
        if ($unknown === []) {
            return;
        }

        throw new MappingException(
            message: 'Unexpected input key.',
            path: $unknown[0],
            expectedType: $meta->className,
            givenValue: $unknown,
        );
    }
}
