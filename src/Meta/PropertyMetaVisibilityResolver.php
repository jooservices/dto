<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Attributes\Redact;
use JOOservices\Dto\Exceptions\HydrationException;

final class PropertyMetaVisibilityResolver
{
    /**
     * @param  list<object>  $attributes
     *
     * @throws HydrationException
     */
    public function assertHiddenRedactCompatible(string $propertyName, array $attributes): void
    {
        if ($this->resolveRedact($attributes) !== null && $this->resolveHidden($attributes) === PropertyMeta::HIDDEN) {
            throw new HydrationException(
                message: 'A property cannot combine #[Hidden] and #[Redact]. Pick one.',
                path: $propertyName,
            );
        }
    }

    /**
     * @param  list<object>  $attributes
     */
    public function resolveHidden(array $attributes): int
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Hidden) {
                return PropertyMeta::HIDDEN;
            }
        }

        return PropertyMeta::VISIBLE;
    }

    /**
     * @param  list<object>  $attributes
     */
    public function resolveRedact(array $attributes): ?string
    {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Redact) {
                return $attribute->with;
            }
        }

        return null;
    }
}
