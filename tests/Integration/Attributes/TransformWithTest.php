<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureTransformed;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

/**
 * TransformWith runs during serialization (Normalizer), not hydration — the raw property
 * value stays untouched, only toArray()/toJson() output is transformed.
 */
final class TransformWithTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testRawPropertyValueIsUnaffectedByTransformWith(): void
    {
        $dto = AttrFixtureTransformed::fromArray(['label' => 'joo']);

        self::assertSame('joo', $dto->label);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testSerializedOutputIsTransformed(): void
    {
        $dto = AttrFixtureTransformed::fromArray(['label' => 'joo']);

        self::assertSame('JOO', $dto->toArray()['label']);
    }
}
