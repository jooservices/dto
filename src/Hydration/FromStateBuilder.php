<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Validation\ObjectValidator;
use ReflectionException;

final class FromStateBuilder
{
    public function __construct(
        private readonly HydrationInstantiator $instantiator,
        private readonly ObjectValidator $validator,
    ) {
    }

    /**
     * @param  array<string, mixed>  $state
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function build(ClassMeta $meta, array $state, Context $context): object
    {
        if ($context->validationEnabled) {
            $this->validator->validateArgs($meta, $state, $context);
        }

        return $this->instantiator->instantiate($meta, $state);
    }
}
