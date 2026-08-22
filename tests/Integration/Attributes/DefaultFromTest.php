<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\DefaultFrom;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureDefaultFrom;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureDefaultFromRequired;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class DefaultFromTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('ATTR_FIXTURE_DEFAULT_FROM_ZONE');
        putenv('ATTR_FIXTURE_REQUIRED_DEFAULT_FROM_ENV');
        parent::tearDown();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testConstructorRejectsNeitherEnvNorMethod(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DefaultFrom();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testConstructorRejectsAnInvalidMethodName(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DefaultFrom(method: '1not-a-valid-name');
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testConstructorAcceptsEnvOnly(): void
    {
        $attribute = new DefaultFrom(env: 'SOME_ENV');

        self::assertSame('SOME_ENV', $attribute->env);
        self::assertNull($attribute->method);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMissingPropertyFallsBackToTheStaticMethod(): void
    {
        $dto = AttrFixtureDefaultFrom::fromArray([]);

        self::assertSame('us-east-1', $dto->region);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testExplicitInputValueBypassesTheDefault(): void
    {
        $dto = AttrFixtureDefaultFrom::fromArray(['region' => 'eu-west-1']);

        self::assertSame('eu-west-1', $dto->region);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMissingPropertyFallsBackToTheEnvironmentVariableWhenSet(): void
    {
        putenv('ATTR_FIXTURE_DEFAULT_FROM_ZONE=zone-9');

        $dto = AttrFixtureDefaultFrom::fromArray(['region' => 'us-east-1']);

        self::assertSame('zone-9', $dto->zone);
    }

    /**
     * Unhappy: env var unset and no value supplied falls through to the property's own
     * nullable default (null) rather than raising an error, because `zone` is nullable.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMissingEnvAndMissingInputFallsBackToPropertyDefaultWhenNullable(): void
    {
        putenv('ATTR_FIXTURE_DEFAULT_FROM_ZONE');

        $dto = AttrFixtureDefaultFrom::fromArray(['region' => 'us-east-1']);

        self::assertNull($dto->zone);
    }

    /**
     * Unhappy: when the property is neither nullable nor has a PHP default, an unresolved
     * DefaultFrom still ends in the ordinary "missing required property" failure.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnresolvedDefaultFromOnARequiredPropertyRaisesMappingException(): void
    {
        putenv('ATTR_FIXTURE_REQUIRED_DEFAULT_FROM_ENV');

        $this->expectException(MappingException::class);

        AttrFixtureDefaultFromRequired::fromArray([]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testResolvedEnvDefaultFromSatisfiesARequiredProperty(): void
    {
        putenv('ATTR_FIXTURE_REQUIRED_DEFAULT_FROM_ENV=secret-token');

        $dto = AttrFixtureDefaultFromRequired::fromArray([]);

        self::assertSame('secret-token', $dto->token);
    }
}
