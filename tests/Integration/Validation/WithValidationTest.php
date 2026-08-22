<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\ValidationFixtureRegexDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class WithValidationTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testWithHonorsValidationWhenContextEnablesIt(): void
    {
        $dto = ValidationFixtureRegexDto::fromArray(['value' => 'abc']);

        $this->expectException(ValidationException::class);
        $dto->with(
            ['value' => '123'],
            new Context(validationEnabled: true, sourceKeyOut: false),
        );
    }
}
