<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Exceptions;

use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Tests\TestCase;

final class MappingExceptionTest extends TestCase
{
    public function test_construct(): void
    {
        $exception = new MappingException('Mapping error');

        $this->assertSame('Mapping error', $exception->getMessage());
    }

    public function test_missing_required_key(): void
    {
        $exception = MappingException::missingRequiredKey('username');

        $this->assertInstanceOf(MappingException::class, $exception);
        $this->assertStringContainsString('username', $exception->getMessage());
        $this->assertStringContainsString('Missing required key', $exception->getMessage());
    }

    public function test_missing_required_key_with_path(): void
    {
        $exception = MappingException::missingRequiredKey('username', 'user.credentials');

        $this->assertSame('user.credentials', $exception->getPath());
        $this->assertStringContainsString('username', $exception->getMessage());
    }

    public function test_invalid_mapping(): void
    {
        $exception = MappingException::invalidMapping('email_address', 'email');

        $this->assertInstanceOf(MappingException::class, $exception);
        $this->assertStringContainsString('email_address', $exception->getMessage());
        $this->assertStringContainsString('email', $exception->getMessage());
        $this->assertStringContainsString('Cannot map', $exception->getMessage());
    }

    public function test_invalid_mapping_with_path(): void
    {
        $exception = MappingException::invalidMapping('email_address', 'email', 'user');

        $this->assertSame('user', $exception->getPath());
        $this->assertStringContainsString('email_address', $exception->getMessage());
        $this->assertStringContainsString('email', $exception->getMessage());
    }

    public function test_missing_required_key_without_path(): void
    {
        $exception = MappingException::missingRequiredKey('name');

        $this->assertSame('', $exception->getPath());
    }

    public function test_invalid_mapping_without_path(): void
    {
        $exception = MappingException::invalidMapping('source', 'target');

        $this->assertSame('', $exception->getPath());
    }

    public function test_exception_inherits_from_jdto_exception(): void
    {
        $exception = new MappingException('Test');

        $this->assertInstanceOf(\JOOservices\Dto\Exceptions\JdtoException::class, $exception);
    }

    public function test_missing_required_key_message_format(): void
    {
        $exception = MappingException::missingRequiredKey('id');

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Missing required key', $message);
        $this->assertStringContainsString("'id'", $message);
    }

    public function test_invalid_mapping_message_format(): void
    {
        $exception = MappingException::invalidMapping('user_id', 'userId');

        $message = $exception->getMessage();

        $this->assertStringStartsWith('Cannot map', $message);
        $this->assertStringContainsString("'user_id'", $message);
        $this->assertStringContainsString("'userId'", $message);
    }
}
