<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Hydration\Naming\SnakeCaseStrategy;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\TestCase;

final class MapperTest extends TestCase
{
    private Mapper $mapper;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mapper = new Mapper;
    }

    public function test_map_direct_property_names(): void
    {
        $meta = $this->createClassMeta([
            'name' => null,
            'age' => null,
        ]);

        $source = [
            'name' => $this->faker->name(),
            'age' => $this->faker->numberBetween(18, 99),
        ];

        $result = $this->mapper->map($source, $meta, null);

        $this->assertSame($source['name'], $result['name']);
        $this->assertSame($source['age'], $result['age']);
    }

    public function test_map_with_map_from_attribute(): void
    {
        $meta = $this->createClassMeta([
            'email' => 'email_address',
        ]);

        $emailValue = $this->faker->email();
        $source = [
            'email_address' => $emailValue,
        ];

        $result = $this->mapper->map($source, $meta, null);

        $this->assertSame($emailValue, $result['email']);
    }

    public function test_map_with_naming_strategy(): void
    {
        $meta = $this->createClassMeta([
            'firstName' => null,
            'lastName' => null,
        ]);

        $context = new Context(namingStrategy: new SnakeCaseStrategy);

        $source = [
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
        ];

        $result = $this->mapper->map($source, $meta, $context);

        $this->assertSame($source['first_name'], $result['firstName']);
        $this->assertSame($source['last_name'], $result['lastName']);
    }

    public function test_map_prefers_map_from_over_naming_strategy(): void
    {
        $meta = $this->createClassMeta([
            'email' => 'user_email',
        ]);

        $context = new Context(namingStrategy: new SnakeCaseStrategy);

        $emailValue = $this->faker->email();
        $source = [
            'user_email' => $emailValue,
            'email' => 'wrong@example.com',
        ];

        $result = $this->mapper->map($source, $meta, $context);

        $this->assertSame($emailValue, $result['email']);
    }

    public function test_map_handles_missing_keys(): void
    {
        $meta = $this->createClassMeta([
            'name' => null,
            'optional' => null,
        ]);

        $source = [
            'name' => $this->faker->name(),
        ];

        $result = $this->mapper->map($source, $meta, null);

        $this->assertSame($source['name'], $result['name']);
        $this->assertArrayNotHasKey('optional', $result);
    }

    public function test_map_handles_null_values(): void
    {
        $meta = $this->createClassMeta([
            'name' => null,
        ]);

        $source = [
            'name' => null,
        ];

        $result = $this->mapper->map($source, $meta, null);

        $this->assertArrayHasKey('name', $result);
        $this->assertNull($result['name']);
    }

    public function test_map_with_empty_source(): void
    {
        $meta = $this->createClassMeta([
            'name' => null,
        ]);

        $result = $this->mapper->map([], $meta, null);

        $this->assertSame([], $result);
    }

    public function test_map_ignores_extra_source_keys(): void
    {
        $meta = $this->createClassMeta([
            'name' => null,
        ]);

        $source = [
            'name' => $this->faker->name(),
            'extra_key' => $this->faker->word(),
            'another_extra' => $this->faker->randomNumber(),
        ];

        $result = $this->mapper->map($source, $meta, null);

        $this->assertCount(1, $result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayNotHasKey('extra_key', $result);
        $this->assertArrayNotHasKey('another_extra', $result);
    }

    public function test_map_falls_back_to_property_name_if_map_from_not_found(): void
    {
        $meta = $this->createClassMeta([
            'email' => 'non_existent_key',
        ]);

        $emailValue = $this->faker->email();
        $source = [
            'email' => $emailValue,
        ];

        $result = $this->mapper->map($source, $meta, null);

        $this->assertSame($emailValue, $result['email']);
    }

    /**
     * @param  array<string, string|null>  $properties  Property name => mapFrom value (null means no mapping)
     */
    private function createClassMeta(array $properties): ClassMeta
    {
        $propertyMetas = [];

        foreach ($properties as $name => $mapFrom) {
            $type = new TypeDescriptor(
                name: 'string',
                isBuiltin: true,
                isNullable: true,
                isArray: false,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            );

            $propertyMetas[$name] = new PropertyMeta(
                name: $name,
                type: $type,
                isReadonly: true,
                hasDefault: false,
                defaultValue: null,
                mapFrom: $mapFrom,
                casterClass: null,
                transformerClass: null,
                isHidden: false,
                validationRules: [],
                attributes: [],
            );
        }

        return new ClassMeta(
            className: 'TestClass',
            isReadonly: true,
            properties: $propertyMetas,
            constructorParams: array_keys($properties),
            attributes: [],
        );
    }
}
