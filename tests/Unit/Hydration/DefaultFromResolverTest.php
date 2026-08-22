<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\DefaultFromResolver;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDefaultFromDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureDefaultFromPrivateMethodDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class DefaultFromResolverTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('COREFIXTURE_TEST_ENV');
        putenv('COREFIXTURE_DEFAULT_AGE');

        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testResolveReturnsEnvValueWhenPresentAndNonEmpty(): void
    {
        putenv('COREFIXTURE_TEST_ENV=from-env');
        $attribute = new DefaultFrom(env: 'COREFIXTURE_TEST_ENV', method: 'defaultAge');

        $result = (new DefaultFromResolver())->resolve($attribute, CoreFixtureDefaultFromDto::class);

        self::assertTrue($result['found']);
        self::assertSame('from-env', $result['value']);
    }

    /**
     * C7: env unset falls through to the static method.
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testResolveFallsThroughToMethodWhenEnvIsUnset(): void
    {
        putenv('COREFIXTURE_TEST_ENV');
        $attribute = new DefaultFrom(env: 'COREFIXTURE_TEST_ENV', method: 'defaultAge');

        $result = (new DefaultFromResolver())->resolve($attribute, CoreFixtureDefaultFromDto::class);

        self::assertTrue($result['found']);
        self::assertSame(42, $result['value']);
    }

    /**
     * C7: an empty-string env value is treated as absent, not a real value.
     *
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testResolveTreatsEmptyStringEnvAsAbsent(): void
    {
        putenv('COREFIXTURE_TEST_ENV=');
        $attribute = new DefaultFrom(env: 'COREFIXTURE_TEST_ENV', method: 'defaultAge');

        $result = (new DefaultFromResolver())->resolve($attribute, CoreFixtureDefaultFromDto::class);

        self::assertSame(42, $result['value']);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testResolveReturnsNotFoundWhenMethodMissingAndNoEnv(): void
    {
        $attribute = new DefaultFrom(method: 'noSuchMethod');

        $result = (new DefaultFromResolver())->resolve($attribute, CoreFixtureDefaultFromDto::class);

        self::assertFalse($result['found']);
        self::assertNull($result['value']);
    }

    /**
     * @throws InvalidArgumentException
     * @throws ReflectionException
     */
    public function testResolveIgnoresNonPublicStaticMethods(): void
    {
        $attribute = new DefaultFrom(method: 'privateFallback');

        $result = (new DefaultFromResolver())->resolve(
            $attribute,
            CoreFixtureDefaultFromPrivateMethodDto::class,
        );

        self::assertFalse($result['found']);
        self::assertNull($result['value']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIntegrationHydratesViaMethodFallbackWhenSourceMissingKey(): void
    {
        putenv('COREFIXTURE_DEFAULT_AGE');

        $dto = CoreFixtureDefaultFromDto::fromArray([]);

        self::assertSame(42, $dto->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIntegrationEnvValueWinsOverMethodAndIsCastToInt(): void
    {
        putenv('COREFIXTURE_DEFAULT_AGE=7');

        $dto = CoreFixtureDefaultFromDto::fromArray([]);

        self::assertSame(7, $dto->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIntegrationExplicitValueBypassesDefaultFromEntirely(): void
    {
        putenv('COREFIXTURE_DEFAULT_AGE=99');

        $dto = CoreFixtureDefaultFromDto::fromArray(['age' => 3]);

        self::assertSame(3, $dto->age);
    }
}
