<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Core;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
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
}
