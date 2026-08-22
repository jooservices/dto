<?php

declare(strict_types=1);

namespace JOOservices\Dto\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\Validation\ValidationRuleAttribute;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\ClassMeta;
use ReflectionException;

final class ValidatorRegistry implements ValidatorRegistryInterface
{
    /** @var list<ValidatorInterface> */
    private array $validators = [];

    public function register(ValidatorInterface $validator): void
    {
        $this->validators[] = $validator;
    }

    /**
     * @param  array<string, mixed>  $values
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function validateProperty(
        ClassMeta $meta,
        string $propertyName,
        mixed $value,
        array $values,
        ?Context $ctx,
    ): void {
        $property = $meta->property($propertyName);
        if ($property === null) {
            return;
        }

        $violations = [];
        foreach ($property->resolved->validationRules as $attribute) {
            if (! $attribute instanceof ValidationRuleAttribute) {
                continue;
            }

            foreach ($this->validators as $validator) {
                if (! $validator->supports($attribute)) {
                    continue;
                }

                try {
                    $validator->validate($attribute, $meta, $propertyName, $value, $values, $ctx);
                } catch (ValidationException $exception) {
                    foreach ($exception->getViolations() as $violation) {
                        $violations[] = $violation;
                    }
                }
            }
        }

        if ($violations !== []) {
            throw new ValidationException(
                message: 'Validation failed.',
                path: $propertyName,
                violations: $violations,
            );
        }
    }
}
