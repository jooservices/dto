<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes\Validation;

use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Tests\TestCase;

final class EmailTest extends TestCase
{
    public function test_default_message(): void
    {
        $email = new Email;

        $this->assertSame('The value must be a valid email address', $email->getMessage());
    }

    public function test_custom_message(): void
    {
        $customMessage = 'Please provide a valid email';
        $email = new Email(message: $customMessage);

        $this->assertSame($customMessage, $email->getMessage());
    }

    public function test_message_property_is_null_by_default(): void
    {
        $email = new Email;

        $this->assertNull($email->message);
    }

    public function test_message_property_is_set_when_provided(): void
    {
        $customMessage = 'Invalid work email';
        $email = new Email(message: $customMessage);

        $this->assertSame($customMessage, $email->message);
    }
}
