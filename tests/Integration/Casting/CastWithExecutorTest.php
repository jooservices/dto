<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\CastWith;
use JOOservices\Dto\Casting\CastWithExecutor;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureCastWithDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureCastWithInvalidDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureCastWithMissingClassDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureCastWithOptionsDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureNotACaster;
use JOOservices\Dto\Tests\Fixtures\CastingFixturePrefixCaster;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureUppercaseCaster;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class CastWithExecutorTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testExecutesTheReferencedCasterWithNoOptions(): void
    {
        $executor = new CastWithExecutor();
        $property = $this->stringProperty();

        $result = $executor->execute(new CastWith(CastingFixtureUppercaseCaster::class), $property, 'hello', null);

        self::assertSame('HELLO', $result);
    }

    /**
     * Constructor-spread `options`: a named-key options array is spread as
     * named constructor arguments onto the caster class.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSpreadsNamedOptionsIntoTheCasterConstructor(): void
    {
        $executor = new CastWithExecutor();
        $property = $this->stringProperty();
        $attribute = new CastWith(CastingFixturePrefixCaster::class, options: ['prefix' => 'X-']);

        self::assertSame('X-val', $executor->execute($attribute, $property, 'val', null));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsCasterClassNotImplementingCasterInterface(): void
    {
        $executor = new CastWithExecutor();
        $property = $this->stringProperty();

        $this->expectException(CastException::class);
        $executor->execute(new CastWith(CastingFixtureNotACaster::class), $property, 'val', null);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsUnknownCasterClass(): void
    {
        $executor = new CastWithExecutor();
        $property = $this->stringProperty();

        /** @phpstan-ignore argument.type */
        $attribute = new CastWith('JOOservices\Dto\Tests\Fixtures\CastingFixtureNoSuchCasterClass');

        $this->expectException(InvalidArgumentException::class);
        $executor->execute($attribute, $property, 'val', null);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoHydrationRoutesThroughCastWithAttribute(): void
    {
        $dto = CastingFixtureCastWithDto::fromArray(['label' => 'hello']);
        self::assertSame('HELLO', $dto->label);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoHydrationSpreadsCastWithOptions(): void
    {
        $dto = CastingFixtureCastWithOptionsDto::fromArray(['label' => 'val']);
        self::assertSame('X-val', $dto->label);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoHydrationRejectsCasterNotImplementingInterface(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureCastWithInvalidDto::fromArray(['label' => 'val']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDtoHydrationRejectsUnknownCasterClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CastingFixtureCastWithMissingClassDto::fromArray(['label' => 'val']);
    }

    private function stringProperty(): PropertyMeta
    {
        return PropertyMetaBuilder::make(
            name: 'label',
            type: TypeDescriptor::builtin('string'),
        );
    }
}
