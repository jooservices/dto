<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Casting;

use InvalidArgumentException;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\Casting\CastingFixtureTypedArrayOfDtoDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureArrayOrStringDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureAssocMixedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureAssocStringArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureConstructorParamArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureNonDtoNestedDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureTypedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureUnionNumberDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureUntypedArrayDto;
use JOOservices\Dto\Tests\Fixtures\CastingFixtureVarWinsOverParamDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionHolderDto;
use JOOservices\Dto\Tests\Fixtures\IntersectionTypedDto;
use JOOservices\Dto\Tests\Fixtures\MetaBagDto;
use JOOservices\Dto\Tests\Fixtures\ProfileDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class ValueTypeCasterTest extends TestCase
{
    /**
     * C9: a value that is already an exact match for one union member is
     * returned untouched (no coercion happens at all).
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnionKeepsAnExactIntMatchAsInt(): void
    {
        $dto = CastingFixtureUnionNumberDto::fromArray(['value' => 5]);
        self::assertIsInt($dto->value);
        self::assertSame(5, $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnionKeepsAnExactFloatMatchAsFloat(): void
    {
        $dto = CastingFixtureUnionNumberDto::fromArray(['value' => 5.5]);
        self::assertIsFloat($dto->value);
        self::assertSame(5.5, $dto->value);
    }

    /**
     * C9: for an ambiguous value (not an exact match for either member),
     * `int` is ranked before `float` and is therefore tried first.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnionPrefersIntOverFloatForAnAmbiguousValue(): void
    {
        $dto = CastingFixtureUnionNumberDto::fromArray(['value' => true]);
        self::assertIsInt($dto->value);
        self::assertSame(1, $dto->value);

        $dto = CastingFixtureUnionNumberDto::fromArray(['value' => '5']);
        self::assertIsInt($dto->value);
        self::assertSame(5, $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUnionThrowsWhenNoMemberCanCastTheValue(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureUnionNumberDto::fromArray(['value' => ['nope']]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTypedArrayCastsEachScalarItem(): void
    {
        $dto = CastingFixtureTypedArrayDto::fromArray(['numbers' => ['1', '2', '3']]);
        self::assertSame([1, 2, 3], $dto->numbers);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTypedArrayAcceptsAnEmptyArray(): void
    {
        $dto = CastingFixtureTypedArrayDto::fromArray(['numbers' => []]);
        self::assertSame([], $dto->numbers);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTypedArrayRejectsANonArrayValue(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureTypedArrayDto::fromArray(['numbers' => 'nope']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTypedArrayPropagatesAnItemCastFailure(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureTypedArrayDto::fromArray(['numbers' => ['abc']]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testTypedArrayHydratesNestedDtoItems(): void
    {
        $dto = CastingFixtureTypedArrayOfDtoDto::fromArray([
            'addresses' => [['city' => 'Hanoi'], ['city' => 'Saigon']],
        ]);

        self::assertCount(2, $dto->addresses);
        self::assertInstanceOf(AddressDto::class, $dto->addresses[0]);
        self::assertSame('Hanoi', $dto->addresses[0]->city);
        self::assertSame('Saigon', $dto->addresses[1]->city);
    }

    /**
     * Untyped `array` properties must hydrate as a pass-through. Reflection
     * describes them as KIND_BUILTIN `array`, which previously fell through
     * to the caster registry and threw "No caster matched the value".
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUntypedArrayPassesValuesThrough(): void
    {
        $dto = CastingFixtureUntypedArrayDto::fromArray(['categories' => [1, 2, 3]]);

        self::assertSame([1, 2, 3], $dto->categories);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUntypedArrayUsesConstructorDefaultWhenOmitted(): void
    {
        $dto = CastingFixtureUntypedArrayDto::fromArray([]);

        self::assertSame([], $dto->categories);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUntypedArrayRejectsANonArrayValue(): void
    {
        $this->expectException(CastException::class);
        CastingFixtureUntypedArrayDto::fromArray(['categories' => 'nope']);
    }

    /**
     * Constructor `@param array<string, mixed>` is mixed, so values pass through.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testUntypedArrayHydratesAssociativeValues(): void
    {
        $dto = MetaBagDto::fromArray(['meta' => ['theme' => 'dark', 'count' => 3]]);

        self::assertSame(['theme' => 'dark', 'count' => 3], $dto->meta);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testConstructorParamTagsCastNamedArrayProperties(): void
    {
        $dto = CastingFixtureConstructorParamArrayDto::fromArray([
            'ids' => ['1', '2'],
            'labels' => ['24' => 24, '48' => 'https://example.test/48.png'],
        ]);

        self::assertSame([1, 2], $dto->ids);
        self::assertSame(['24' => '24', '48' => 'https://example.test/48.png'], $dto->labels);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPropertyVarTagWinsOverConstructorParamWhenCasting(): void
    {
        $dto = CastingFixtureVarWinsOverParamDto::fromArray(['values' => ['1', '2']]);

        self::assertSame([1, 2], $dto->values);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAssociativeMixedArrayPassesValuesThrough(): void
    {
        $dto = CastingFixtureAssocMixedArrayDto::fromArray([
            'meta' => ['theme' => 'dark', 'ids' => [1, 2], 'ok' => true],
        ]);

        self::assertSame(['theme' => 'dark', 'ids' => [1, 2], 'ok' => true], $dto->meta);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testAssociativeStringArrayCastsValuesAndKeepsKeys(): void
    {
        $dto = CastingFixtureAssocStringArrayDto::fromArray([
            'avatarUrls' => ['24' => 24, '48' => 'https://example.test/48.png'],
        ]);

        self::assertSame(
            ['24' => '24', '48' => 'https://example.test/48.png'],
            $dto->avatarUrls,
        );
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayOrStringUnionKeepsAnArrayMatch(): void
    {
        $dto = CastingFixtureArrayOrStringDto::fromArray(['value' => [1, 2]]);

        self::assertSame([1, 2], $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayOrStringUnionKeepsAStringMatch(): void
    {
        $dto = CastingFixtureArrayOrStringDto::fromArray(['value' => 'hello']);

        self::assertSame('hello', $dto->value);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNestedDtoHydratesFromArray(): void
    {
        $dto = ProfileDto::fromArray([
            'user' => ['name' => 'Joo', 'age' => 30],
            'address' => ['city' => 'Hanoi'],
        ]);

        self::assertSame('Joo', $dto->user->name);
        self::assertSame('Hanoi', $dto->address->city);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNestedDtoPassesThroughAnAlreadyHydratedInstance(): void
    {
        $address = new AddressDto(city: 'Hanoi');

        $dto = ProfileDto::fromArray([
            'user' => ['name' => 'Joo', 'age' => 30],
            'address' => $address,
        ]);

        self::assertSame($address, $dto->address);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNestedDtoHydratesFromAJsonString(): void
    {
        $dto = ProfileDto::fromArray([
            'user' => ['name' => 'Joo', 'age' => 30],
            'address' => '{"city":"Hanoi"}',
        ]);

        self::assertSame('Hanoi', $dto->address->city);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testNestedDtoRejectsAScalarSourceItCannotHydrateFrom(): void
    {
        $this->expectException(CastException::class);
        ProfileDto::fromArray([
            'user' => ['name' => 'Joo', 'age' => 30],
            'address' => 42,
        ]);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testClassTypedPropertyRejectsWhenTargetIsNotADto(): void
    {
        $this->expectException(HydrationException::class);
        CastingFixtureNonDtoNestedDto::fromArray(['payload' => ['a' => 1]]);
    }

    /**
     * Intersection types are passed through completely untouched: no caster
     * is invoked and no interface conformance is checked by the library.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIntersectionTypePassesThroughACompatibleValueUnchanged(): void
    {
        $subject = new IntersectionHolderDto(name: 'abc');

        $dto = IntersectionTypedDto::fromArray(['subject' => $subject]);

        self::assertSame($subject, $dto->subject);
    }

    /**
     * Intersection types are validated during casting; incompatible values
     * raise CastException before the constructor runs.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testIntersectionTypeRejectsIncompatibleValueDuringCasting(): void
    {
        $this->expectException(CastException::class);
        IntersectionTypedDto::fromArray(['subject' => 'not-an-object']);
    }
}
