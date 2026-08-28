<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Core;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureStringDto;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureTransformInputDto;
use JOOservices\Dto\Tests\Fixtures\FakeServerRequest;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use ReflectionException;
use stdClass;

final class HydratesFromInputTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromRequestMergesQueryAndArrayBody(): void
    {
        $request = new FakeServerRequest(
            parsedBody: ['name' => 'from-body'],
            queryParams: ['age' => '30'],
        );

        $dto = UserDto::fromRequest($request);

        self::assertSame('from-body', $dto->name);
        self::assertSame(30, $dto->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromRequestAcceptsStdClassParsedBody(): void
    {
        $body = new stdClass();
        $body->name = 'from-object-body';

        $request = new FakeServerRequest(
            parsedBody: $body,
            queryParams: ['age' => '21'],
        );

        $dto = UserDto::fromRequest($request);

        self::assertSame('from-object-body', $dto->name);
        self::assertSame(21, $dto->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromRequestNullBodyKeepsQueryOnly(): void
    {
        $request = new FakeServerRequest(
            parsedBody: null,
            queryParams: ['name' => 'from-query', 'age' => '18'],
        );

        $dto = UserDto::fromRequest($request);

        self::assertSame('from-query', $dto->name);
        self::assertSame(18, $dto->age);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromJsonAppliesTransformInput(): void
    {
        $dto = CoreFixtureTransformInputDto::fromJson('{"name":"ada"}');

        self::assertSame('ADA', $dto->name);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromObjectAppliesTransformInput(): void
    {
        $object = new stdClass();
        $object->name = 'ada';

        $dto = CoreFixtureTransformInputDto::fromObject($object);

        self::assertSame('ADA', $dto->name);
    }

    /**
     * JSON numbers larger than 2^53 must hydrate onto string properties without
     * being coerced through a lossy float.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromJsonKeepsBigintAsString(): void
    {
        $dto = CastingFixtureStringDto::fromJson('{"value":9223372036854775808}');

        self::assertSame('9223372036854775808', $dto->value);
    }
}
