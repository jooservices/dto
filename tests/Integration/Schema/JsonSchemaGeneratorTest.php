<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Schema;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Schema\JsonSchemaGenerator;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureCircle;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureMapped;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureShape;
use JOOservices\Dto\Tests\Fixtures\AttrFixtureSquare;
use JOOservices\Dto\Tests\Fixtures\EnumHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionTypedDto;
use JOOservices\Dto\Tests\Fixtures\NoConstructorDto;
use JOOservices\Dto\Tests\Fixtures\PriorityDto;
use JOOservices\Dto\Tests\Fixtures\ProfileDto;
use JOOservices\Dto\Tests\Fixtures\SchemaFixtureNode;
use JOOservices\Dto\Tests\Fixtures\SecretDto;
use JOOservices\Dto\Tests\Fixtures\UnionHolderDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class JsonSchemaGeneratorTest extends TestCase
{
    private function generator(): JsonSchemaGenerator
    {
        return new JsonSchemaGenerator(new MetaFactory());
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
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    private function defsOf(array $schema): array
    {
        return $this->asArray($schema['$defs'] ?? null);
    }

    /**
     * @param  array<string, mixed>  $defs
     * @return array<string, mixed>
     */
    private function def(array $defs, string $className): array
    {
        $key = $this->defKey($className);
        self::assertArrayHasKey($key, $defs);

        return $this->asArray($defs[$key]);
    }

    /**
     * @param  array<string, mixed>  $def
     * @return array<string, mixed>
     */
    private function properties(array $def): array
    {
        return $this->asArray($def['properties'] ?? null);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testGeneratesRootRefAndSchemaEnvelope(): void
    {
        $schema = $this->generator()->generate(UserDto::class);

        self::assertSame('https://json-schema.org/draft/2020-12/schema', $schema['$schema']);
        self::assertSame('#/$defs/' . $this->defKey(UserDto::class), $schema['$ref']);
        self::assertArrayHasKey($this->defKey(UserDto::class), $this->defsOf($schema));
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testRequiredExcludesNullableAndDefaultedProperties(): void
    {
        $schema = $this->generator()->generate(UserDto::class);
        $def = $this->def($this->defsOf($schema), UserDto::class);
        $properties = $this->properties($def);

        self::assertSame(['name', 'age'], $def['required']);
        self::assertSame(['type' => 'string'], $properties['name']);
        self::assertSame(['type' => 'integer'], $properties['age']);
        self::assertSame(
            ['anyOf' => [['type' => 'string'], ['type' => 'null']]],
            $properties['email'],
        );
    }

    /**
     * Nested DTOs are hoisted into $defs and referenced by $ref rather than inlined.
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testNestedDtoPropertiesBecomeRefsIntoSharedDefs(): void
    {
        $schema = $this->generator()->generate(ProfileDto::class);
        $userKey = $this->defKey(UserDto::class);
        $addressKey = $this->defKey(AddressDto::class);

        $defs = $this->defsOf($schema);
        self::assertArrayHasKey($userKey, $defs);
        self::assertArrayHasKey($addressKey, $defs);

        $profileDef = $this->def($defs, ProfileDto::class);
        $properties = $this->properties($profileDef);
        self::assertSame(['$ref' => '#/$defs/' . $userKey], $properties['user']);
        self::assertSame(['$ref' => '#/$defs/' . $addressKey], $properties['address']);
        self::assertSame(['user', 'address'], $profileDef['required']);
    }

    /**
     * Weird/edge: a DTO that references its own type must not recurse forever. The generator
     * pre-registers a placeholder in $defs before walking properties, so the self-reference
     * resolves to a $ref instead of looping.
     *
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testSelfReferencingDtoProducesRefCycleWithoutInfiniteRecursion(): void
    {
        $schema = $this->generator()->generate(SchemaFixtureNode::class);
        $key = $this->defKey(SchemaFixtureNode::class);

        $def = $this->def($this->defsOf($schema), SchemaFixtureNode::class);
        $properties = $this->properties($def);
        self::assertSame(['label'], $def['required']);
        self::assertSame(
            [
                'anyOf' => [
                    ['$ref' => '#/$defs/' . $key],
                    ['type' => 'null'],
                ],
            ],
            $properties['parent'],
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testUnionPropertyBecomesAnyOfSchema(): void
    {
        $schema = $this->generator()->generate(UnionHolderDto::class);
        $def = $this->def($this->defsOf($schema), UnionHolderDto::class);
        $properties = $this->properties($def);

        self::assertSame(
            ['anyOf' => [['type' => 'string'], ['type' => 'integer']]],
            $properties['value'],
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testStringBackedEnumPropertyUsesStringSchemaWithEnumValues(): void
    {
        $schema = $this->generator()->generate(EnumHolderDto::class);
        $def = $this->def($this->defsOf($schema), EnumHolderDto::class);
        $properties = $this->properties($def);

        self::assertSame(
            ['type' => 'string', 'enum' => ['active', 'inactive']],
            $properties['status'],
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testIntBackedEnumPropertyUsesIntegerSchemaWithEnumValues(): void
    {
        $schema = $this->generator()->generate(PriorityDto::class);
        $def = $this->def($this->defsOf($schema), PriorityDto::class);
        $properties = $this->properties($def);

        self::assertSame(
            ['type' => 'integer', 'enum' => [1, 10]],
            $properties['priority'],
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testMapToRenamesSchemaPropertyKey(): void
    {
        $schema = $this->generator()->generate(AttrFixtureMapped::class);
        $def = $this->def($this->defsOf($schema), AttrFixtureMapped::class);
        $properties = $this->properties($def);

        self::assertArrayHasKey('displayName', $properties);
        self::assertArrayNotHasKey('name', $properties);
        self::assertSame(['displayName', 'age'], $def['required']);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testRedactedPropertiesRemainInSchema(): void
    {
        $schema = $this->generator()->generate(SecretDto::class);
        $def = $this->def($this->defsOf($schema), SecretDto::class);
        $properties = $this->properties($def);

        self::assertSame(['type' => 'string'], $properties['password']);
        self::assertSame(
            ['anyOf' => [['type' => 'string'], ['type' => 'null']]],
            $properties['apiToken'],
        );
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testIntersectionPropertyUsesAllOfSchema(): void
    {
        $schema = $this->generator()->generate(IntersectionTypedDto::class);
        $def = $this->def($this->defsOf($schema), IntersectionTypedDto::class);
        $properties = $this->properties($def);

        $subject = $this->asArray($properties['subject']);
        $allOf = array_values($this->asArray($subject['allOf']));

        self::assertCount(2, $allOf);
        self::assertSame(['type' => 'object'], $allOf[0]);
        self::assertSame(['type' => 'object'], $allOf[1]);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testDiscriminatorMapBuildsOneOfAndSubtypeGraph(): void
    {
        $schema = $this->generator()->generate(AttrFixtureShape::class);
        $defs = $this->defsOf($schema);
        $def = $this->def($defs, AttrFixtureShape::class);

        self::assertArrayHasKey('oneOf', $def);
        self::assertArrayHasKey('discriminator', $def);

        $discriminator = $this->asArray($def['discriminator']);
        $mapping = $this->asArray($discriminator['mapping']);
        self::assertSame('type', $discriminator['propertyName']);
        self::assertSame(
            '#/$defs/' . $this->defKey(AttrFixtureCircle::class),
            $mapping['circle'],
        );
        self::assertArrayHasKey('base', $mapping);
        self::assertArrayNotHasKey('invalid', $mapping);
        self::assertCount(2, $this->asArray($def['oneOf']));

        $circleDef = $this->def($defs, AttrFixtureCircle::class);
        self::assertSame(['type' => 'number'], $this->properties($circleDef)['radius']);

        $squareDef = $this->def($defs, AttrFixtureSquare::class);
        self::assertSame(['type' => 'number'], $this->properties($squareDef)['side']);
    }

    /**
     * @throws HydrationException
     * @throws ReflectionException
     */
    public function testConstructorLessDtoProducesEmptyObjectSchema(): void
    {
        $schema = $this->generator()->generate(NoConstructorDto::class);
        $def = $this->def($this->defsOf($schema), NoConstructorDto::class);

        self::assertSame('object', $def['type']);
        self::assertSame([], $def['properties']);
        self::assertSame([], $def['required']);
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
