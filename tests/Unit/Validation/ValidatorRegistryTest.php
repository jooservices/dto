<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRegistryDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class ValidatorRegistryTest extends TestCase
{
    /**
     * A value that fails two independent rule attributes on the same
     * property (#[Email] and #[Regex]) must accumulate both violations into
     * a single ValidationException, rather than stopping at the first one.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     */
    public function testViolationsFromMultipleRulesOnSamePropertyAreAggregated(): void
    {
        try {
            ValidationFixtureRegistryDto::fromArray(
                ['value' => 'abc'],
                new Context(validationEnabled: true, sourceKeyOut: false),
            );
            self::fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            $rules = array_map(
                static fn($violation): string => $violation->rule,
                $exception->getViolations(),
            );

            self::assertCount(2, $rules);
            self::assertContains('email', $rules);
            self::assertContains('regex', $rules);
        }
    }

    /**
     * When a value satisfies every rule attribute on the property, no
     * violation is raised at all.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValueSatisfyingAllRulesPasses(): void
    {
        $dto = ValidationFixtureRegistryDto::fromArray(
            ['value' => null],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );

        self::assertNull($dto->value);
    }
}
