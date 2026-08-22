<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class CasterRegistryTest extends TestCase
{
    public function testReturnsNullWhenNoCasterIsRegistered(): void
    {
        $registry = new CasterRegistry();
        $property = PropertyMetaBuilder::make(
            name: 'x',
            type: TypeDescriptor::builtin('int'),
        );

        self::assertNull($registry->firstMatching($property->type, $property, 1, null));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testReturnsFirstCasterThatSupportsTheProperty(): void
    {
        $registry = new CasterRegistry();
        $property = PropertyMetaBuilder::make(
            name: 'x',
            type: TypeDescriptor::builtin('int'),
        );

        $never = new class implements CasterInterface {
            public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
            {
                unset($type, $property, $value, $ctx);

                return false;
            }

            public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                unset($type, $property, $ctx);

                return $value;
            }
        };
        $first = new class implements CasterInterface {
            public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
            {
                unset($type, $property, $value, $ctx);

                return true;
            }

            public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                unset($type, $property, $ctx);

                /** @phpstan-ignore cast.string */
                return 'first:' . (string) $value;
            }
        };
        $second = new class implements CasterInterface {
            public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
            {
                unset($type, $property, $value, $ctx);

                return true;
            }

            public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
            {
                unset($type, $property, $ctx);

                /** @phpstan-ignore cast.string */
                return 'second:' . (string) $value;
            }
        };

        $registry->register($never);
        $registry->register($first);
        $registry->register($second);

        $matched = $registry->firstMatching($property->type, $property, 7, null);
        self::assertNotNull($matched);
        self::assertSame('first:7', $matched->cast($property->type, $property, 7, null));
    }
}
