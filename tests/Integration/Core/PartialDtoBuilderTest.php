<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Core;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureMapFromDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class PartialDtoBuilderTest extends TestCase
{
    /**
     * C6: allowedFields are SOURCE keys (post-MapFrom target keys), not
     * property names -- every field not explicitly allowed is dropped.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromFiltersBySourceKeyAndDropsEverythingElse(): void
    {
        $builder = CoreFixtureMapFromDto::partial(['full_name', 'age']);

        $dto = $builder->from(['full_name' => 'Jane', 'age' => 5, 'extraneous' => 'dropped']);

        self::assertSame('Jane', $dto->name);
        self::assertSame(5, $dto->age);
    }

    /**
     * C6: listing the PROPERTY name ("name") instead of the actual source key
     * ("full_name") does not include the field at all -- proving the filter
     * really operates on source keys, not on property names.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testFromDoesNotMatchByPropertyNameWhenSourceKeyDiffers(): void
    {
        $builder = CoreFixtureMapFromDto::partial(['name']);

        $this->expectException(MappingException::class);

        $builder->from(['full_name' => 'Jane', 'age' => 5]);
    }

    public function testGetAllowedFieldsReturnsTheConstructorValue(): void
    {
        $builder = CoreFixtureMapFromDto::partial(['full_name', 'age']);

        self::assertSame(['full_name', 'age'], $builder->getAllowedFields());
    }
}
