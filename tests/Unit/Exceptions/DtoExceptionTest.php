<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\ExceptionPayloadRedactorInterface;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\RuleViolation;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Tests\TestCase;

final class DtoExceptionTest extends TestCase
{
    public function testMappingExceptionPathHelpersAndPayload(): void
    {
        $mapping = new MappingException(
            message: 'Type mismatch',
            path: 'name',
            expectedType: 'string',
            givenType: 'int',
            givenValue: 1,
        );

        $withPath = $mapping->withPath('user.name');
        self::assertSame('user.name', $withPath->getPath());
        self::assertSame('root.user.name', $withPath->prependPath('root')->getPath());

        $redacted = $mapping->withPayloadRedactor(new readonly class implements ExceptionPayloadRedactorInterface {
            public function redact(string $path, mixed $value): mixed
            {
                return '[redacted:' . $path . ']';
            }
        });

        self::assertSame('[redacted:name]', $redacted->toArray()['givenValue']);
        self::assertStringContainsString('(path: name)', $mapping->getFullMessage());
    }

    public function testHydrationAndValidationExceptionsExposeCollections(): void
    {
        $mapping = new MappingException(message: 'bad', path: 'field');
        $hydration = new HydrationException(message: 'failed', errors: [$mapping]);
        self::assertCount(1, $hydration->getErrors());
        self::assertArrayHasKey('errors', $hydration->toArray());

        $validation = new ValidationException(
            message: 'invalid',
            path: 'name',
            violations: [new RuleViolation('name', 'required', 'missing', '')],
        );
        self::assertCount(1, $validation->getViolations());
        self::assertArrayHasKey('violations', $validation->toArray());

        $cast = new CastException(message: 'bad cast', path: 'age');
        self::assertSame('age', $cast->getPath());
    }
}
