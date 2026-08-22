<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\DiscriminatorMap;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\DiscriminatorResolver;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDiscriminatorCircleDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDiscriminatorShapeDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureNotADto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class DiscriminatorResolverTest extends TestCase
{
    /**
     * @param  array<string, class-string>  $map
     */
    private function metaWithMap(array $map): ClassMeta
    {
        return new ClassMeta(
            className: CoreFixtureDiscriminatorShapeDto::class,
            properties: [],
            ctorParams: [],
            hasConstructor: true,
            discriminator: new DiscriminatorMap(field: 'kind', map: $map),
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testResolveReturnsSameMetaUnchangedWhenNoDiscriminatorDeclared(): void
    {
        $meta = new ClassMeta(
            className: CoreFixtureDiscriminatorShapeDto::class,
            properties: [],
            ctorParams: [],
            hasConstructor: true,
        );

        $resolved = (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => 'anything'], null);

        self::assertSame($meta, $resolved);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testResolveThrowsWhenDiscriminatorFieldMissingFromMappedData(): void
    {
        $meta = $this->metaWithMap(['circle' => CoreFixtureDiscriminatorCircleDto::class]);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Missing discriminator field.');

        (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, [], null);
    }

    /**
     * @throws ReflectionException
     */
    public function testResolveThrowsForUnknownDiscriminatorValue(): void
    {
        $meta = $this->metaWithMap(['circle' => CoreFixtureDiscriminatorCircleDto::class]);

        try {
            (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => 'triangle'], null);
            self::fail('Expected HydrationException.');
        } catch (HydrationException $exception) {
            self::assertSame('Unknown discriminator value.', $exception->getMessage());
            self::assertSame('triangle', $exception->givenValue);
        }
    }

    /**
     * Weird/edge case: a non-scalar discriminator value can never match a map
     * key, and must be rejected rather than silently coerced.
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testResolveTreatsNonScalarDiscriminatorValueAsUnknown(): void
    {
        $meta = $this->metaWithMap(['circle' => CoreFixtureDiscriminatorCircleDto::class]);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Unknown discriminator value.');

        (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => ['nested']], null);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testResolveKeepsBaseMetaWhenMapEntryPointsToTheBaseClassItself(): void
    {
        $meta = $this->metaWithMap(['base' => CoreFixtureDiscriminatorShapeDto::class]);

        $resolved = (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => 'base'], null);

        self::assertSame($meta, $resolved);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testResolveSwitchesToValidSubclassMeta(): void
    {
        $meta = $this->metaWithMap(['circle' => CoreFixtureDiscriminatorCircleDto::class]);

        $resolved = (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => 'circle'], null);

        self::assertSame(CoreFixtureDiscriminatorCircleDto::class, $resolved->className);
        self::assertNotNull($resolved->property('radius'));
    }

    /**
     * S4: a map entry pointing at a DTO that is not actually a subclass of the
     * requested base must be rejected, not silently instantiated.
     *
     * @throws ReflectionException
     */
    public function testResolveRejectsTargetThatIsNotASubclassOfTheBase(): void
    {
        $meta = $this->metaWithMap(['other' => UserDto::class]);

        try {
            (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => 'other'], null);
            self::fail('Expected HydrationException.');
        } catch (HydrationException $exception) {
            self::assertSame('Discriminator target is not a DTO subclass.', $exception->getMessage());
            self::assertSame(UserDto::class, $exception->givenType);
        }
    }

    /**
     * S4: a map entry pointing at a class that is not a DTO at all must be rejected.
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testResolveRejectsTargetThatIsNotADtoAtAll(): void
    {
        $meta = $this->metaWithMap(['plain' => CoreFixtureNotADto::class]);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Discriminator target is not a DTO subclass.');

        (new DiscriminatorResolver(new MetaFactory()))->resolve($meta, ['kind' => 'plain'], null);
    }

    /**
     * C3/C17: the discriminator must be read from the *mapped* value (after
     * MapFrom renames the source key), not from a raw source key that never
     * even matches the discriminator field name.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testDiscriminatorResolvesAfterMapFromRenamesTheSourceKey(): void
    {
        $dto = CoreFixtureDiscriminatorShapeDto::from(['type' => 'circle', 'label' => 'c1', 'radius' => 2.5]);

        self::assertInstanceOf(CoreFixtureDiscriminatorCircleDto::class, $dto);
        self::assertSame('circle', $dto->kind);
        self::assertSame('c1', $dto->label);
        self::assertSame(2.5, $dto->radius);
    }
}
