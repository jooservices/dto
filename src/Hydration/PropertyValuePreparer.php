<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Casting\ValueCaster;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use ReflectionException;

final class PropertyValuePreparer
{
    public function __construct(
        private readonly PipelineRunner $pipelines,
        private readonly ValueCaster $valueCaster,
    ) {
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function prepare(PropertyMeta $property, mixed $value, Context $ctx): mixed
    {
        $value = $this->pipelines->applyGlobal($value, $ctx);
        foreach ($property->resolved->pipelines as $pipeline) {
            $value = $this->pipelines->applyProperty($value, $pipeline);
        }

        return $this->valueCaster->cast($property, $value, $ctx);
    }
}
