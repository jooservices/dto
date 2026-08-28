<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\PackageInfo;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\Support\FakeUser;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use ReflectionException;

final class PackageSmokeTest extends TestCase
{
    public function testAutoloadResolvesPackageInfo(): void
    {
        self::assertSame('jooservices/dto', PackageInfo::name());
        self::assertSame('3.2.0', PackageInfo::version());
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
    public function testDtoRoundTripWithWrap(): void
    {
        $payload = FakeUser::payload($this->faker());
        $dto = UserDto::fromJson(json_encode($payload, JSON_THROW_ON_ERROR));
        $json = $dto->toJson(new Context(
            validationEnabled: false,
            sourceKeyOut: false,
            serializationOptions: new SerializationOptions(wrap: 'data'),
        ));
        $expected = json_encode(['data' => $payload], JSON_THROW_ON_ERROR);
        self::assertSame($expected, $json);
    }
}
