<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Schema;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Schema\OpenApiGenerator;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\ProfileDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;
use stdClass;

final class OpenApiGeneratorTest extends TestCase
{
    private function generator(): OpenApiGenerator
    {
        return new OpenApiGenerator(new MetaFactory());
    }

    private function defKey(string $className): string
    {
        return str_replace('\\', '.', $className);
    }

    /**
     * @return array<string, mixed>
     */
    private function asArray(mixed $value): array
    {
        self::assertIsArray($value);

        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testGeneratesOpenApiEnvelope(): void
    {
        $document = $this->generator()->generate(UserDto::class);
        $info = $this->asArray($document['info']);

        self::assertSame('3.1.0', $document['openapi']);
        self::assertSame('DTO schemas', $info['title']);
        self::assertInstanceOf(stdClass::class, $document['paths']);
        self::assertSame(
            ['$ref' => '#/components/schemas/' . $this->defKey(UserDto::class)],
            $document['x-root'],
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testComponentsSchemasHoldSameDefsAsJsonSchemaGenerator(): void
    {
        $document = $this->generator()->generate(ProfileDto::class);
        $components = $this->asArray($document['components']);
        $schemas = $this->asArray($components['schemas']);

        $profileKey = $this->defKey(ProfileDto::class);
        $userKey = $this->defKey(UserDto::class);
        $addressKey = $this->defKey(AddressDto::class);

        self::assertArrayHasKey($profileKey, $schemas);
        $profileDef = $this->asArray($schemas[$profileKey]);
        $properties = $this->asArray($profileDef['properties']);

        self::assertSame(['$ref' => '#/components/schemas/' . $userKey], $properties['user']);
        self::assertSame(['$ref' => '#/components/schemas/' . $addressKey], $properties['address']);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testUnknownClassNameRaisesHydrationException(): void
    {
        $this->expectException(HydrationException::class);

        /** @phpstan-ignore argument.type (deliberately passing a non-existent class name) */
        $this->generator()->generate('JOOservices\\Dto\\Tests\\Fixtures\\DoesNotExist');
    }
}
