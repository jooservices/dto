<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

/**
 * Classes permitted inside serialized {@see ClassMeta} cache payloads.
 */
final class MetaCacheAllowedClasses
{
    /**
     * @return list<class-string>
     */
    public function all(): array
    {
        return [
            'JOOservices\Dto\Meta\ClassMeta',
            'JOOservices\Dto\Meta\PropertyMeta',
            'JOOservices\Dto\Meta\PropertyMetaResolvedAttributes',
            'JOOservices\Dto\Meta\TypeDescriptor',
            'JOOservices\Dto\Attributes\CastWith',
            'JOOservices\Dto\Attributes\DefaultFrom',
            'JOOservices\Dto\Attributes\DiscriminatorMap',
            'JOOservices\Dto\Attributes\Hidden',
            'JOOservices\Dto\Attributes\MapFrom',
            'JOOservices\Dto\Attributes\MapTo',
            'JOOservices\Dto\Attributes\Pipeline',
            'JOOservices\Dto\Attributes\Redact',
            'JOOservices\Dto\Attributes\StrictType',
            'JOOservices\Dto\Attributes\TransformWith',
            'JOOservices\Dto\Attributes\Validation\Between',
            'JOOservices\Dto\Attributes\Validation\Email',
            'JOOservices\Dto\Attributes\Validation\Length',
            'JOOservices\Dto\Attributes\Validation\Max',
            'JOOservices\Dto\Attributes\Validation\Min',
            'JOOservices\Dto\Attributes\Validation\Regex',
            'JOOservices\Dto\Attributes\Validation\Required',
            'JOOservices\Dto\Attributes\Validation\RequiredIf',
            'JOOservices\Dto\Attributes\Validation\Url',
            'JOOservices\Dto\Attributes\Validation\Valid',
        ];
    }
}
