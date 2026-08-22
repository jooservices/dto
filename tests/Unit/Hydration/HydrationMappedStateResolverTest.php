<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Hydration\DiscriminatorResolver;
use JOOservices\Dto\Hydration\HydrationMappedStateResolver;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Hydration\UnknownKeyGuard;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDiscriminatorCircleDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDiscriminatorShapeDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class HydrationMappedStateResolverTest extends TestCase
{
    private function resolver(): HydrationMappedStateResolver
    {
        $mapper = new Mapper();

        return new HydrationMappedStateResolver(
            $mapper,
            new DiscriminatorResolver(new MetaFactory()),
            new UnknownKeyGuard($mapper),
        );
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     */
    public function testResolveRemapsUsingSubclassPropertiesAfterDiscriminatorSwitch(): void
    {
        $metaFactory = new MetaFactory();
        $baseMeta = $metaFactory->create(CoreFixtureDiscriminatorShapeDto::class);
        $ctx = new Context(false, false);

        $state = $this->resolver()->resolve($baseMeta, ['type' => 'circle', 'label' => 's', 'radius' => 3.5], $ctx);

        self::assertSame(CoreFixtureDiscriminatorCircleDto::class, $state['meta']->className);
        self::assertSame('circle', $state['mapped']['kind']);
        self::assertSame(3.5, $state['mapped']['radius']);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     */
    public function testResolveThrowsHydrationExceptionForUnmappedDiscriminatorValue(): void
    {
        $metaFactory = new MetaFactory();
        $baseMeta = $metaFactory->create(CoreFixtureDiscriminatorShapeDto::class);
        $ctx = new Context(false, false);

        $this->expectException(HydrationException::class);

        $this->resolver()->resolve($baseMeta, ['type' => 'triangle'], $ctx);
    }

    /**
     * Unknown-key rejection runs against the *resolved* (subclass) meta, using
     * the original raw source -- so a key valid for the subclass is accepted
     * while a truly unexpected key is still rejected.
     *
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     */
    public function testResolveRejectsUnknownKeysAfterRemapUnderStrictContext(): void
    {
        $metaFactory = new MetaFactory();
        $baseMeta = $metaFactory->create(CoreFixtureDiscriminatorShapeDto::class);
        $ctx = Context::strict();

        $this->expectException(MappingException::class);

        $this->resolver()->resolve($baseMeta, ['type' => 'circle', 'radius' => 1.0, 'bogus' => true], $ctx);
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     */
    public function testResolveAcceptsSubclassOnlyKeysUnderStrictContext(): void
    {
        $metaFactory = new MetaFactory();
        $baseMeta = $metaFactory->create(CoreFixtureDiscriminatorShapeDto::class);
        $ctx = Context::strict();

        $state = $this->resolver()->resolve($baseMeta, ['type' => 'circle', 'radius' => 1.0], $ctx);

        self::assertSame(CoreFixtureDiscriminatorCircleDto::class, $state['meta']->className);
    }
}
