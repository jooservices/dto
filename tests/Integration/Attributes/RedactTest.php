<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\Fixtures\HiddenAndRedactConflictDto;
use JOOservices\Dto\Tests\Fixtures\SecretDto;
use JOOservices\Dto\Tests\Fixtures\SecretNumericDto;
use JOOservices\Dto\Tests\Fixtures\SecretValidatedDto;
use JOOservices\Dto\Tests\TestCase;
use JsonException;
use ReflectionException;

final class RedactTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testToArrayMasksRedactedPropertiesWithCustomValue(): void
    {
        $dto = SecretDto::fromArray([
            'username' => 'joo',
            'password' => 'super-secret',
            'apiToken' => 'sk-live-123',
        ]);

        $array = $dto->toArray();

        self::assertSame('joo', $array['username']);
        self::assertSame('***REDACTED***', $array['password']);
        self::assertSame('****', $array['apiToken']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testToJsonMasksRedactedProperties(): void
    {
        $dto = SecretDto::fromArray(['username' => 'joo', 'password' => 'super-secret']);

        $decoded = json_decode($dto->toJson(), true, flags: JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);

        self::assertSame('***REDACTED***', $decoded['password']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testHiddenAndRedactOnSamePropertyIsRejected(): void
    {
        $this->expectException(HydrationException::class);
        HiddenAndRedactConflictDto::fromArray(['secret' => 'x']);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws JsonException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testStateViewOperationsSeeTheRealSecretValue(): void
    {
        $dto = SecretDto::fromArray(['username' => 'joo', 'password' => 'super-secret']);

        // clone()/with() go through the state view (not the masked serialization view).
        $copy = $dto->with(username: 'other');
        self::assertSame('super-secret', $copy->password);

        $sameSecret = SecretDto::fromArray(['username' => 'joo', 'password' => 'super-secret']);
        $differentSecret = SecretDto::fromArray(['username' => 'joo', 'password' => 'different']);

        self::assertTrue($dto->equals($sameSecret));
        self::assertFalse($dto->equals($differentSecret));
        self::assertNotSame($dto->hash(), $differentSecret->hash());

        $differentUsername = SecretDto::fromArray(['username' => 'other', 'password' => 'super-secret']);
        self::assertNotSame($dto->hash(), $differentUsername->hash());
    }

    /**
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testCastExceptionOnRedactedPropertyDoesNotLeakRawValue(): void
    {
        try {
            SecretNumericDto::fromArray(['pin' => ['not', 'a', 'number']]);
            self::fail('Expected CastException.');
        } catch (CastException $exception) {
            self::assertSame('***REDACTED***', $exception->givenValue);
            self::assertSame('***REDACTED***', $exception->toArray()['givenValue']);
        }
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testValidationExceptionOnRedactedPropertyDoesNotLeakRawValue(): void
    {
        $dto = SecretValidatedDto::fromArray(
            ['password' => 'short'],
            new Context(validationEnabled: false, sourceKeyOut: false),
        );

        try {
            $dto->validate();
            self::fail('Expected ValidationException.');
        } catch (ValidationException $exception) {
            self::assertSame('***REDACTED***', $exception->getViolations()[0]->value);
        }
    }
}
