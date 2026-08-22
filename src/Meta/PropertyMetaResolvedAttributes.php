<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Attributes\StrictType;
use JOOservices\Dto\Attributes\TransformWith;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;

/**
 * Precomputed attribute-derived fields for {@see PropertyMeta}.
 */
final readonly class PropertyMetaResolvedAttributes
{
    /**
     * @param  list<Pipeline>  $pipelines
     * @param  list<TransformWith>  $transformWith
     * @param  list<ValidationRuleAttribute>  $validationRules
     * @param  list<string>  $sourceKeys
     */
    public function __construct(
        public ?string $mapFromKey = null,
        public ?string $mapToKey = null,
        public ?CastWith $castWith = null,
        public ?StrictType $strictType = null,
        public ?DefaultFrom $defaultFrom = null,
        public array $pipelines = [],
        public array $transformWith = [],
        public array $validationRules = [],
        public array $sourceKeys = [],
    ) {
    }
}
