<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureMapped;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

/**
 * Real hydration/serialization coverage for MapFrom (input key aliasing) and MapTo (output
 * key aliasing) via AttrFixtureMapped.
 */
final class MappingAttributesTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMapFromReadsValueFromTheAliasedInputKey(): void
    {
        $dto = AttrFixtureMapped::fromArray(['full_name' => 'Ada', 'age' => 30]);

        self::assertSame('Ada', $dto->name);
    }

    /**
     * The property's own name still works as a fallback source key alongside the alias.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPropertyNameStillWorksAsAFallbackSourceKey(): void
    {
        $dto = AttrFixtureMapped::fromArray(['name' => 'Grace', 'age' => 40]);

        self::assertSame('Grace', $dto->name);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMapToRenamesTheKeyOnSerializationOutput(): void
    {
        $dto = AttrFixtureMapped::fromArray(['full_name' => 'Ada', 'age' => 30]);

        $array = $dto->toArray();

        self::assertArrayHasKey('displayName', $array);
        self::assertArrayNotHasKey('name', $array);
        self::assertSame('Ada', $array['displayName']);
    }

    /**
     * Unhappy: missing both the alias and the property-name fallback fails hydration.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMissingBothAliasAndPropertyNameFailsHydration(): void
    {
        $this->expectException(MappingException::class);

        AttrFixtureMapped::fromArray(['age' => 30]);
    }

    /**
     * MapTo wins over sourceKeyOut naming-strategy output, since it is checked first in
     * NormalizerPropertySupport::outputKey().
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testMapToTakesPrecedenceEvenWithSourceKeyOutEnabled(): void
    {
        $dto = AttrFixtureMapped::fromArray(['full_name' => 'Ada', 'age' => 30]);

        $array = $dto->toArray(new Context(validationEnabled: false, sourceKeyOut: true));

        self::assertSame('Ada', $array['displayName']);
    }
}
