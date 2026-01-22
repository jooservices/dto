<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

/**
 * Interface for property value transformation steps.
 *
 * Pipeline steps process property values during hydration,
 * before casting and validation occur.
 */
interface PipelineStepInterface
{
    /**
     * Process a property value.
     *
     * @param  mixed  $value  The current property value
     * @param  PropertyMeta  $property  Property metadata
     * @param  Context|null  $ctx  Hydration context
     * @return mixed The transformed value
     */
    public function process(mixed $value, PropertyMeta $property, ?Context $ctx): mixed;
}
