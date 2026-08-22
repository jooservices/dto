<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use JOOservices\Dto\Attributes\DefaultFrom;
use ReflectionException;
use ReflectionMethod;

final class DefaultFromResolver
{
    /**
     * @param  class-string  $className
     * @return array{found: bool, value: mixed}
     *
     * @throws ReflectionException
     */
    public function resolve(DefaultFrom $attribute, string $className): array
    {
        if ($attribute->env !== null) {
            $env = getenv($attribute->env);
            if ($env !== false && $env !== '') {
                return ['found' => true, 'value' => $env];
            }
        }

        if ($attribute->method !== null && method_exists($className, $attribute->method)) {
            $reflection = new ReflectionMethod($className, $attribute->method);
            if ($reflection->isPublic() && $reflection->isStatic()) {
                $method = $attribute->method;
                /** @var callable():mixed $callable */
                $callable = [$className, $method];

                return ['found' => true, 'value' => $callable()];
            }
        }

        return ['found' => false, 'value' => null];
    }
}
