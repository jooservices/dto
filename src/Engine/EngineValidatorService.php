<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\MetaFactoryInterface;
use JOOservices\Dto\Validation\ObjectValidator;
use ReflectionException;

final class EngineValidatorService
{
    public function __construct(
        private readonly MetaFactoryInterface $metaFactory,
        private readonly ObjectValidator $validator,
    ) {
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validate(object $instance, Context $ctx): void
    {
        $meta = $this->metaFactory->create($instance::class);
        $this->validator->validateInstance($instance, $meta, $ctx);
    }
}
