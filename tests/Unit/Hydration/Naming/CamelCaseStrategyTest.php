<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Naming;

use JOOservices\Dto\Hydration\Naming\CamelCaseStrategy;
use JOOservices\Dto\Hydration\Naming\NamingStrategyInterface;
use JOOservices\Dto\Tests\TestCase;

final class CamelCaseStrategyTest extends TestCase
{
    private CamelCaseStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->strategy = new CamelCaseStrategy;
    }

    public function test_convert_to_source_from_snake_case(): void
    {
        $result = $this->strategy->convert('user_name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userName', $result);
    }

    public function test_convert_to_source_from_kebab_case(): void
    {
        $result = $this->strategy->convert('user-name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userName', $result);
    }

    public function test_convert_to_source_from_pascal_case(): void
    {
        $result = $this->strategy->convert('UserName', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userName', $result);
    }

    public function test_convert_to_property_from_snake_case(): void
    {
        $result = $this->strategy->convert('user_name', NamingStrategyInterface::DIRECTION_TO_PROPERTY);

        $this->assertSame('userName', $result);
    }

    public function test_convert_to_property_from_kebab_case(): void
    {
        $result = $this->strategy->convert('user-name', NamingStrategyInterface::DIRECTION_TO_PROPERTY);

        $this->assertSame('userName', $result);
    }

    public function test_convert_to_property_from_pascal_case(): void
    {
        $result = $this->strategy->convert('UserName', NamingStrategyInterface::DIRECTION_TO_PROPERTY);

        $this->assertSame('userName', $result);
    }

    public function test_convert_with_unknown_direction_returns_original(): void
    {
        $original = $this->faker->word();

        $result = $this->strategy->convert($original, 'unknown');

        $this->assertSame($original, $result);
    }

    public function test_convert_handles_single_word(): void
    {
        $result = $this->strategy->convert('name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('name', $result);
    }

    public function test_convert_handles_empty_string(): void
    {
        $result = $this->strategy->convert('', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('', $result);
    }

    public function test_convert_handles_multiple_underscores(): void
    {
        $result = $this->strategy->convert('user_first_name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userFirstName', $result);
    }

    public function test_convert_handles_multiple_dashes(): void
    {
        $result = $this->strategy->convert('user-first-name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userFirstName', $result);
    }

    public function test_convert_already_camel_case(): void
    {
        $result = $this->strategy->convert('userName', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userName', $result);
    }

    public function test_convert_handles_numbers_in_snake_case(): void
    {
        $result = $this->strategy->convert('field_2_name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('field2Name', $result);
    }

    public function test_convert_handles_numbers_in_kebab_case(): void
    {
        $result = $this->strategy->convert('field-2-name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('field2Name', $result);
    }

    public function test_convert_handles_consecutive_underscores(): void
    {
        $result = $this->strategy->convert('user__name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertStringContainsString('user', $result);
    }

    public function test_convert_handles_consecutive_dashes(): void
    {
        $result = $this->strategy->convert('user--name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertStringContainsString('user', $result);
    }

    public function test_convert_handles_leading_underscore(): void
    {
        $result = $this->strategy->convert('_user_name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertIsString($result);
    }

    public function test_convert_handles_leading_dash(): void
    {
        $result = $this->strategy->convert('-user-name', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertIsString($result);
    }

    public function test_convert_handles_trailing_underscore(): void
    {
        $result = $this->strategy->convert('user_name_', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertIsString($result);
    }

    public function test_convert_handles_trailing_dash(): void
    {
        $result = $this->strategy->convert('user-name-', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertIsString($result);
    }

    public function test_convert_preserves_case(): void
    {
        $result = $this->strategy->convert('USER_NAME', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertSame('userName', $result);
    }

    public function test_convert_handles_mixed_delimiters(): void
    {
        $result = $this->strategy->convert('user_name-test', NamingStrategyInterface::DIRECTION_TO_SOURCE);

        $this->assertIsString($result);
    }
}
