<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

final class ObjectValidator
{
    public function __construct(
        private readonly ValidatorRegistryInterface $registry,
    ) {
    }

    /**
     * @param  array<string, mixed>  $values
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validateArgs(ClassMeta $meta, array $values, ?Context $ctx): void
    {
        $caught = [];
        foreach ($meta->properties as $property) {
            try {
                $this->registry->validateProperty(
                    $meta,
                    $property->name,
                    $values[$property->name] ?? null,
                    $values,
                    $ctx,
                );
            } catch (ValidationException $exception) {
                foreach ($exception->getViolations() as $violation) {
                    $caught[] = $violation;
                }
            }
        }

        if ($caught !== []) {
            throw new ValidationException(
                message: 'Validation failed.',
                violations: $caught,
            );
        }
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validateInstance(object $instance, ClassMeta $meta, ?Context $ctx): void
    {
        /** @var array<string, mixed> $values */
        $values = get_object_vars($instance);
        $this->validateArgs($meta, $values, $ctx);
    }
}
