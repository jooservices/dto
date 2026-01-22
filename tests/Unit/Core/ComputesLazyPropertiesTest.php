<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use Closure;
use JOOservices\Dto\Core\ComputesLazyProperties;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\Fixtures\LazyUserDto;
use JOOservices\Dto\Tests\TestCase;

final class ComputesLazyPropertiesTest extends TestCase
{
    public function test_interface_exists(): void
    {
        $this->assertTrue(interface_exists(ComputesLazyProperties::class));
    }

    public function test_dto_can_implement_interface(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $this->assertInstanceOf(ComputesLazyProperties::class, $dto);
        $this->assertInstanceOf(Dto::class, $dto);
    }

    public function test_compute_lazy_properties_returns_array(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $lazy = $dto->computeLazyProperties();

        $this->assertIsArray($lazy);
        $this->assertArrayHasKey('fullName', $lazy);
        $this->assertArrayHasKey('initials', $lazy);
        $this->assertArrayHasKey('stats', $lazy);
        $this->assertArrayHasKey('displayEmail', $lazy);
    }

    public function test_lazy_properties_can_be_closures(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $lazy = $dto->computeLazyProperties();

        $this->assertInstanceOf(Closure::class, $lazy['initials']);
        $this->assertInstanceOf(Closure::class, $lazy['stats']);
    }

    public function test_lazy_properties_can_be_immediate_values(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $lazy = $dto->computeLazyProperties();

        $this->assertIsString($lazy['fullName']);
        $this->assertSame('John Doe', $lazy['fullName']);
        $this->assertIsString($lazy['displayEmail']);
    }

    public function test_closure_lazy_properties_are_callable(): void
    {
        $dto = new LazyUserDto(
            firstName: 'John',
            lastName: 'Doe',
            email: 'john@example.com',
            age: 30,
        );

        $lazy = $dto->computeLazyProperties();
        $initials = $lazy['initials'];

        $this->assertInstanceOf(Closure::class, $initials);
        $result = $initials();
        $this->assertSame('JD', $result);
    }

    public function test_normal_serialization_excludes_lazy(): void
    {
        $dto = LazyUserDto::from([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $array = $dto->toArray();

        // Regular properties included
        $this->assertArrayHasKey('firstName', $array);
        $this->assertArrayHasKey('lastName', $array);
        $this->assertArrayHasKey('email', $array);
        $this->assertArrayHasKey('age', $array);

        // Lazy properties excluded by default
        $this->assertArrayNotHasKey('fullName', $array);
        $this->assertArrayNotHasKey('initials', $array);
        $this->assertArrayNotHasKey('stats', $array);
        $this->assertArrayNotHasKey('displayEmail', $array);
    }

    public function test_serialization_with_specific_lazy_property(): void
    {
        $dto = LazyUserDto::from([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $options = new SerializationOptions()->withIncludeLazy(['fullName']);
        $context = new Context(serializationOptions: $options);
        $array = $dto->toArray($context);

        // Lazy property included
        $this->assertArrayHasKey('fullName', $array);
        $this->assertSame('John Doe', $array['fullName']);

        // Other lazy properties excluded
        $this->assertArrayNotHasKey('initials', $array);
        $this->assertArrayNotHasKey('stats', $array);
    }

    public function test_serialization_with_multiple_lazy_properties(): void
    {
        $dto = LazyUserDto::from([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $options = new SerializationOptions()->withIncludeLazy(['fullName', 'initials']);
        $context = new Context(serializationOptions: $options);
        $array = $dto->toArray($context);

        $this->assertArrayHasKey('fullName', $array);
        $this->assertSame('John Doe', $array['fullName']);
        $this->assertArrayHasKey('initials', $array);
        $this->assertSame('JD', $array['initials']);
        $this->assertArrayNotHasKey('stats', $array);
    }

    public function test_serialization_with_all_lazy_properties(): void
    {
        $dto = LazyUserDto::from([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $options = new SerializationOptions()->withIncludeLazy([]);
        $context = new Context(serializationOptions: $options);
        $array = $dto->toArray($context);

        // All lazy properties included
        $this->assertArrayHasKey('fullName', $array);
        $this->assertArrayHasKey('initials', $array);
        $this->assertArrayHasKey('stats', $array);
        $this->assertArrayHasKey('displayEmail', $array);

        // Check values
        $this->assertSame('John Doe', $array['fullName']);
        $this->assertSame('JD', $array['initials']);
        $this->assertIsArray($array['stats']);
        $this->assertSame(7, $array['stats']['nameLength']);
        $this->assertSame('example.com', $array['stats']['emailDomain']);
        $this->assertTrue($array['stats']['isAdult']);
    }

    public function test_lazy_property_respects_only_filter(): void
    {
        $dto = LazyUserDto::from([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $options = new SerializationOptions()
            ->withIncludeLazy(['fullName', 'initials'])
            ->withOnly(['firstName', 'fullName']);
        $context = new Context(serializationOptions: $options);
        $array = $dto->toArray($context);

        // Only firstName and fullName included
        $this->assertArrayHasKey('firstName', $array);
        $this->assertArrayHasKey('fullName', $array);

        // Excluded by only filter
        $this->assertArrayNotHasKey('lastName', $array);
        $this->assertArrayNotHasKey('email', $array);
        $this->assertArrayNotHasKey('initials', $array); // Lazy but not in 'only'
    }

    public function test_lazy_property_respects_except_filter(): void
    {
        $dto = LazyUserDto::from([
            'firstName' => 'John',
            'lastName' => 'Doe',
            'email' => 'john@example.com',
            'age' => 30,
        ]);

        $options = new SerializationOptions()
            ->withIncludeLazy(['fullName', 'initials'])
            ->withExcept(['fullName']);
        $context = new Context(serializationOptions: $options);
        $array = $dto->toArray($context);

        // fullName excluded by 'except' filter
        $this->assertArrayNotHasKey('fullName', $array);

        // initials included (in lazy list and not in except)
        $this->assertArrayHasKey('initials', $array);
    }
}
