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
        $request = new class {
            /** @return array<string, mixed> */
            public function getParsedBody(): array
            {
                return ['name' => 'from-body'];
            }

            /** @return array<string, mixed> */
            public function getQueryParams(): array
            {
                return ['age' => '30'];
            }
        };

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

        $request = new class ($body) {
            public function __construct(private readonly stdClass $body)
            {
            }

            public function getParsedBody(): stdClass
            {
                return $this->body;
            }

            /** @return array<string, mixed> */
            public function getQueryParams(): array
            {
                return ['age' => '21'];
            }
        };

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
    public function testFromRequestRejectsNonPsr7Request(): void
    {
        $this->expectException(HydrationException::class);
        UserDto::fromRequest(new stdClass());
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
    public function testFromRequestDecodesJsonStringParsedBody(): void
    {
        $request = new class {
            public function getParsedBody(): string
            {
                return '{"name":"from-json-body"}';
            }

            /** @return array<string, mixed> */
            public function getQueryParams(): array
            {
                return ['age' => '30'];
            }
        };

        $dto = UserDto::fromRequest($request);

        self::assertSame('from-json-body', $dto->name);
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
    public function testFromRequestNullBodyKeepsQueryOnly(): void
    {
        $request = new class {
            public function getParsedBody(): mixed
            {
                return null;
            }

            /** @return array<string, mixed> */
            public function getQueryParams(): array
            {
                return ['name' => 'from-query', 'age' => '18'];
            }
        };

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
