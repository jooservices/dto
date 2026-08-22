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
use JOOservices\Dto\Meta\PropertyMeta;
use ReflectionException;

final class HydrationArgumentsBuilder
{
    public function __construct(
        private readonly DefaultFromResolver $defaults,
        private readonly PropertyValuePreparer $preparer,
    ) {
    }

    /**
     * @param  array<string, mixed>  $mapped
     * @return array<string, mixed>
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function build(ClassMeta $meta, array $mapped, Context $ctx): array
    {
        $args = [];
        foreach ($meta->properties as $property) {
            if (array_key_exists($property->name, $mapped)) {
                $args[$property->name] = $this->preparer->prepare($property, $mapped[$property->name], $ctx);
                continue;
            }

            $fromDefault = $this->resolveDefaultFrom($meta->className, $property);
            if ($fromDefault['found']) {
                $args[$property->name] = $this->preparer->prepare($property, $fromDefault['value'], $ctx);
                continue;
            }

            if ($property->hasDefault) {
                $args[$property->name] = $property->defaultValue;
                continue;
            }

            if ($property->allowsNull) {
                $args[$property->name] = null;
                continue;
            }

            throw new MappingException(
                message: 'Missing required property.',
                path: $property->name,
                expectedType: $meta->className,
            );
        }

        return $args;
    }

    /**
     * @return array{found: bool, value: mixed}
     *
     * @throws ReflectionException
     */
    private function resolveDefaultFrom(string $className, PropertyMeta $property): array
    {
        $attribute = $property->resolved->defaultFrom;
        if ($attribute !== null) {
            /** @var class-string $className */
            return $this->defaults->resolve($attribute, $className);
        }

        return ['found' => false, 'value' => null];
    }
}
