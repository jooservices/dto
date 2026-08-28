<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureAssocMixedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureConstructorParamArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureTypedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureUntypedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureVarWinsOverParamDto;
use JOOservices\Dto\Tests\Fixtures\EnumHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionTypedDto;
use JOOservices\Dto\Tests\Fixtures\MetaBagDto;
use JOOservices\Dto\Tests\Fixtures\NonPromotedDto;
use JOOservices\Dto\Tests\Fixtures\NonPublicPromotedDto;
use JOOservices\Dto\Tests\Fixtures\UnionHolderDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Assert;
use ReflectionException;

final class MetaFactoryTest extends TestCase
{
    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testCreateBuildsPropertyLookupAndTypedClasses(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());

        $userMeta = $factory->create(UserDto::class);
        Assert::assertNotNull($userMeta->property('name'));
        Assert::assertNull($userMeta->property('missing'));

        $factory->create(EnumHolderDto::class);
        $factory->create(UnionHolderDto::class);
        $factory->create(IntersectionTypedDto::class);
        $factory->create(IntersectionHolderDto::class);

        $enumMeta = $factory->create(EnumHolderDto::class);
        Assert::assertSame('enum', $enumMeta->property('status')?->type->kind);
    }

    /**
     * Native `array` properties are KIND_ARRAY even without a `@var` item type,
     * so hydration and schema generation share the array path.
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testUntypedArrayPropertyIsDescribedAsArrayKind(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(CastingFixtureUntypedArrayDto::class);
        $type = $meta->property('categories')?->type;

        Assert::assertNotNull($type);
        Assert::assertSame(TypeDescriptor::KIND_ARRAY, $type->kind);
        Assert::assertSame([], $type->members);
    }

    /**
     * Constructor `@param` supplies the item type when the promoted property
     * has no `@var`.
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testConstructorParamTagSuppliesArrayItemType(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(MetaBagDto::class);
        $type = $meta->property('meta')?->type;

        Assert::assertNotNull($type);
        Assert::assertSame(TypeDescriptor::KIND_ARRAY, $type->kind);
        Assert::assertCount(1, $type->members);
        Assert::assertSame(TypeDescriptor::KIND_MIXED, $type->members[0]->kind);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testConstructorParamTagsAreMatchedByParameterName(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(CastingFixtureConstructorParamArrayDto::class);
        $ids = $meta->property('ids')?->type;
        $labels = $meta->property('labels')?->type;

        Assert::assertNotNull($ids);
        Assert::assertSame('int', $ids->members[0]->builtin ?? null);
        Assert::assertNotNull($labels);
        Assert::assertSame('string', $labels->members[0]->builtin ?? null);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testPropertyVarTagWinsOverConstructorParamTag(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(CastingFixtureVarWinsOverParamDto::class);
        $type = $meta->property('values')?->type;

        Assert::assertNotNull($type);
        Assert::assertSame('int', $type->members[0]->builtin ?? null);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testTypedArrayPropertyKeepsItemTypeMember(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(CastingFixtureTypedArrayDto::class);
        $type = $meta->property('numbers')?->type;

        Assert::assertNotNull($type);
        Assert::assertSame(TypeDescriptor::KIND_ARRAY, $type->kind);
        Assert::assertCount(1, $type->members);
        Assert::assertSame('int', $type->members[0]->builtin);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testAssociativeMixedArrayPropertyHasMixedItemType(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());
        $meta = $factory->create(CastingFixtureAssocMixedArrayDto::class);
        $type = $meta->property('meta')?->type;

        Assert::assertNotNull($type);
        Assert::assertSame(TypeDescriptor::KIND_ARRAY, $type->kind);
        Assert::assertCount(1, $type->members);
        Assert::assertSame(TypeDescriptor::KIND_MIXED, $type->members[0]->kind);
    }

    /**
     * @throws ReflectionException
     */
    public function testCreateRejectsMissingOrInvalidClasses(): void
    {
        $factory = new MetaFactory(new MemoryMetaCache());

        try {
            $factory->create('Missing\\Class');
            self::fail('Expected HydrationException for missing class');
        } catch (HydrationException $exception) {
            self::assertStringContainsString('Missing\\Class', $exception->getMessage());
        }

        try {
            $factory->create(NonPromotedDto::class);
            self::fail('Expected HydrationException for non-promoted properties');
        } catch (HydrationException $exception) {
            self::assertStringContainsString('public promoted', $exception->getMessage());
        }

        try {
            $factory->create(NonPublicPromotedDto::class);
            self::fail('Expected HydrationException for non-public promoted properties');
        } catch (HydrationException $exception) {
            self::assertStringContainsString('public', $exception->getMessage());
        }
    }
}
