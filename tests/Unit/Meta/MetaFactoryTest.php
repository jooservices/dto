<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Tests\Fixtures\EnumHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionTypedDto;
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
