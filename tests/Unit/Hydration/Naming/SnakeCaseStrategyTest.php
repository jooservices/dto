<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Naming;

use JOOservices\Dto\Hydration\Naming\NamingStrategyInterface;
use JOOservices\Dto\Hydration\Naming\SnakeCaseStrategy;
use JOOservices\Dto\Tests\TestCase;

final class SnakeCaseStrategyTest extends TestCase
{
    private SnakeCaseStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new SnakeCaseStrategy;
    }

    public function test_convert_to_source_from_camel_case(): void
    {
        $this->assertSame('user_name', $this->strategy->convert('userName', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('first_name', $this->strategy->convert('firstName', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('created_at', $this->strategy->convert('createdAt', NamingStrategyInterface::DIRECTION_TO_SOURCE));
    }

    public function test_convert_to_source_from_pascal_case(): void
    {
        $this->assertSame('user_name', $this->strategy->convert('UserName', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('first_name', $this->strategy->convert('FirstName', NamingStrategyInterface::DIRECTION_TO_SOURCE));
    }

    public function test_convert_to_source_with_multiple_uppercase(): void
    {
        $this->assertSame('html_parser', $this->strategy->convert('HTMLParser', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('api_response', $this->strategy->convert('APIResponse', NamingStrategyInterface::DIRECTION_TO_SOURCE));
    }

    public function test_convert_to_source_with_single_word(): void
    {
        $this->assertSame('name', $this->strategy->convert('name', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('id', $this->strategy->convert('id', NamingStrategyInterface::DIRECTION_TO_SOURCE));
    }

    public function test_convert_to_property_from_snake_case(): void
    {
        $this->assertSame('userName', $this->strategy->convert('user_name', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
        $this->assertSame('firstName', $this->strategy->convert('first_name', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
        $this->assertSame('createdAt', $this->strategy->convert('created_at', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
    }

    public function test_convert_to_property_with_multiple_underscores(): void
    {
        $this->assertSame('userFirstName', $this->strategy->convert('user_first_name', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
    }

    public function test_convert_to_property_with_single_word(): void
    {
        $this->assertSame('name', $this->strategy->convert('name', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
        $this->assertSame('id', $this->strategy->convert('ID', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
    }

    public function test_convert_with_unknown_direction_returns_original(): void
    {
        $original = $this->faker->word();

        $this->assertSame($original, $this->strategy->convert($original, 'unknown'));
    }

    public function test_convert_preserves_already_snake_case_for_source(): void
    {
        $this->assertSame('already_snake', $this->strategy->convert('already_snake', NamingStrategyInterface::DIRECTION_TO_SOURCE));
    }

    public function test_convert_handles_empty_string(): void
    {
        $this->assertSame('', $this->strategy->convert('', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('', $this->strategy->convert('', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
    }

    public function test_convert_handles_numbers_in_name(): void
    {
        $this->assertSame('field2_name', $this->strategy->convert('field2Name', NamingStrategyInterface::DIRECTION_TO_SOURCE));
        $this->assertSame('field2Name', $this->strategy->convert('field2_name', NamingStrategyInterface::DIRECTION_TO_PROPERTY));
    }
}
