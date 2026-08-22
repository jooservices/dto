<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ObjectValidator;
use ReflectionException;

final class HydrationInstanceBuilder
{
    public function __construct(
        private readonly HydrationArgumentsBuilder $arguments,
        private readonly HydrationInstantiator $instantiator,
        private readonly ObjectValidator $validator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $mapped
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function build(ClassMeta $meta, array $mapped, Context $ctx): object
    {
        $args = $this->arguments->build($meta, $mapped, $ctx);

        if ($ctx->validationEnabled) {
            $this->validator->validateArgs($meta, $args, $ctx);
        }

        return $this->instantiator->instantiate($meta, $args);
    }
}
