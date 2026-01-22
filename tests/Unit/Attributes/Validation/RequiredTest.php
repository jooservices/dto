<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes\Validation;

use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Tests\TestCase;

final class RequiredTest extends TestCase
{
    public function test_default_message(): void
    {
        $required = new Required;

        $this->assertSame('This field is required', $required->getMessage());
    }

    public function test_custom_message(): void
    {
        $customMessage = 'Email is mandatory';
        $required = new Required(message: $customMessage);

        $this->assertSame($customMessage, $required->getMessage());
    }

    public function test_message_property_is_null_by_default(): void
    {
        $required = new Required;

        $this->assertNull($required->message);
    }

    public function test_message_property_is_set_when_provided(): void
    {
        $customMessage = 'Name is required';
        $required = new Required(message: $customMessage);

        $this->assertSame($customMessage, $required->message);
    }
}
