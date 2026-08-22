<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureBetweenDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureEmailDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureInnerDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureMaxDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureMinDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRegexDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRequiredDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRequiredIfDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureUrlDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureValidDto;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureValidListDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class ValidationRulesIntegrationTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValidFixturesHydrateAndValidate(): void
    {
        $ctx = new Context(validationEnabled: true, sourceKeyOut: false);

        ValidationFixtureEmailDto::fromArray(['email' => 'user@example.com'], $ctx)->validate();
        ValidationFixtureUrlDto::fromArray(['url' => 'https://example.com'], $ctx)->validate();
        ValidationFixtureRegexDto::fromArray(['value' => 'abc'], $ctx)->validate();
        ValidationFixtureMinDto::fromArray(['value' => '1234567890'], $ctx)->validate();
        ValidationFixtureMaxDto::fromArray(['value' => '50'], $ctx)->validate();
        ValidationFixtureBetweenDto::fromArray(['score' => '5'], $ctx)->validate();
        ValidationFixtureRequiredDto::fromArray(['value' => 'joo'], $ctx)->validate();
        ValidationFixtureRequiredIfDto::fromArray(['active' => false], $ctx)->validate();

        $inner = ValidationFixtureInnerDto::fromArray(['name' => 'ok'], Context::loose());
        ValidationFixtureValidDto::fromArray(['inner' => $inner], Context::loose())->validate();
        ValidationFixtureValidListDto::fromArray(
            ['items' => [ValidationFixtureInnerDto::fromArray(['name' => 'a'], Context::loose())]],
            Context::loose(),
        )->validate();

        self::addToAssertionCount(1);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testInvalidValuesRaiseValidationException(): void
    {
        $this->expectException(ValidationException::class);

        ValidationFixtureEmailDto::fromArray(
            ['email' => 'not-an-email'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
