<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Support;

use JOOservices\Dto\Attributes\Hidden;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\PropertyMetaAttributeResolver;
use JOOservices\Dto\Meta\TypeDescriptor;

final class PropertyMetaBuilder
{
    /**
     * @param  list<object>  $attributes
     */
    public static function make(
        string $name,
        TypeDescriptor $type,
        bool $allowsNull = false,
        bool $hasDefault = false,
        array $attributes = [],
        int $hidden = 0,
        ?string $redactWith = null,
    ): PropertyMeta {
        foreach ($attributes as $attribute) {
            if ($attribute instanceof Hidden) {
                $hidden = PropertyMeta::HIDDEN;
            }
        }

        $resolved = (new PropertyMetaAttributeResolver())->resolve($name, $attributes);

        return new PropertyMeta(
            name: $name,
            type: $type,
            allowsNull: $allowsNull,
            hasDefault: $hasDefault,
            hidden: $hidden,
            attributes: $attributes,
            redactWith: $redactWith,
            resolved: $resolved,
        );
    }
}
