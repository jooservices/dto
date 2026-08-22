<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Casting\Casters\EnumCaster;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureLevel;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureLevelDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureSuit;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureSuitDto;
use JOOservices\Dto\Tests\Fixtures\EnumHolderDto;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class EnumCasterTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsValidBackedStringValue(): void
    {
        $dto = EnumHolderDto::fromArray(['status' => 'active']);
        self::assertSame(Status::Active, $dto->status);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsEnumInstanceDirectly(): void
    {
        $dto = EnumHolderDto::fromArray(['status' => Status::Inactive]);
        self::assertSame(Status::Inactive, $dto->status);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsValidBackedIntValue(): void
    {
        $dto = CastingFixtureLevelDto::fromArray(['level' => 2]);
        self::assertSame(CastingFixtureLevel::High, $dto->level);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsUnknownBackedValue(): void
    {
        try {
            EnumHolderDto::fromArray(['status' => 'archived']);
            self::fail('Expected CastException.');
        } catch (CastException $exception) {
            self::assertSame(Status::class, $exception->expectedType);
            self::assertSame('string', $exception->givenType);
            self::assertSame('archived', $exception->givenValue);
        }
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsNonScalarValue(): void
    {
        $this->expectException(CastException::class);
        EnumHolderDto::fromArray(['status' => ['active']]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAcceptsPureEnumInstance(): void
    {
        $dto = CastingFixtureSuitDto::fromArray(['suit' => CastingFixtureSuit::Hearts]);
        self::assertSame(CastingFixtureSuit::Hearts, $dto->suit);
    }

    /**
     * A pure (non-backed) enum has no tryFrom(), so any scalar value that is
     * not already an instance of it must be rejected.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRejectsScalarValueForPureEnum(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureSuitDto::fromArray(['suit' => 'Hearts']);
    }

    /**
     * Defensive branch: EnumCaster::cast() is only ever routed to by the
     * registry for KIND_ENUM properties, so a null className cannot occur
     * through normal DTO hydration. Constructed directly to cover it.
     *
     * @throws CastException
     */
    public function testThrowsWhenEnumClassIsUndefined(): void
    {
        $caster = new EnumCaster();
        $type = new TypeDescriptor(kind: TypeDescriptor::KIND_ENUM);
        $property = PropertyMetaBuilder::make(
            name: 'status',
            type: $type,
        );

        $this->expectException(CastException::class);
        $caster->cast($type, $property, 'active', null);
    }
}
