<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Validation;

use JOOservices\Dto\Attributes\Validation\Between;
use JOOservices\Dto\Attributes\Validation\Email;
use JOOservices\Dto\Attributes\Validation\Length;
use JOOservices\Dto\Attributes\Validation\Max;
use JOOservices\Dto\Attributes\Validation\Min;
use JOOservices\Dto\Attributes\Validation\Regex;
use JOOservices\Dto\Attributes\Validation\Required;
use JOOservices\Dto\Attributes\Validation\RequiredIf;
use JOOservices\Dto\Attributes\Validation\Url;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\Dto;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Tests\TestCase;

final class ExtendedValidationIntegrationTest extends TestCase
{
    // =========================================
    // Min Validation Tests
    // =========================================

    public function test_min_validation_passes_for_valid_value(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = MinTestDto::from(['age' => 25], $context);

        $this->assertSame(25, $dto->age);
    }

    public function test_min_validation_passes_for_boundary_value(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = MinTestDto::from(['age' => 18], $context);

        $this->assertSame(18, $dto->age);
    }

    public function test_min_validation_fails_for_value_below_min(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        MinTestDto::from(['age' => 15], $context);
    }

    // =========================================
    // Max Validation Tests
    // =========================================

    public function test_max_validation_passes_for_valid_value(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = MaxTestDto::from(['score' => 80], $context);

        $this->assertSame(80, $dto->score);
    }

    public function test_max_validation_passes_for_boundary_value(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = MaxTestDto::from(['score' => 100], $context);

        $this->assertSame(100, $dto->score);
    }

    public function test_max_validation_fails_for_value_above_max(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        MaxTestDto::from(['score' => 150], $context);
    }

    // =========================================
    // Between Validation Tests
    // =========================================

    public function test_between_validation_passes_for_value_in_range(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = BetweenTestDto::from(['percentage' => 50], $context);

        $this->assertSame(50, $dto->percentage);
    }

    public function test_between_validation_fails_for_value_below_range(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        BetweenTestDto::from(['percentage' => -5], $context);
    }

    public function test_between_validation_fails_for_value_above_range(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        BetweenTestDto::from(['percentage' => 105], $context);
    }

    // =========================================
    // URL Validation Tests
    // =========================================

    public function test_url_validation_passes_for_valid_url(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = UrlTestDto::from(['website' => 'https://example.com'], $context);

        $this->assertSame('https://example.com', $dto->website);
    }

    public function test_url_validation_fails_for_invalid_url(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        UrlTestDto::from(['website' => 'not-a-url'], $context);
    }

    public function test_url_validation_passes_for_null_nullable_field(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = NullableUrlTestDto::from(['website' => null], $context);

        $this->assertNull($dto->website);
    }

    // =========================================
    // Regex Validation Tests
    // =========================================

    public function test_regex_validation_passes_for_matching_pattern(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = RegexTestDto::from(['code' => 'US-1234'], $context);

        $this->assertSame('US-1234', $dto->code);
    }

    public function test_regex_validation_fails_for_non_matching_pattern(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        RegexTestDto::from(['code' => 'invalid-code'], $context);
    }

    // =========================================
    // Length Validation Tests
    // =========================================

    public function test_length_validation_passes_for_valid_length(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = LengthTestDto::from(['username' => 'johndoe'], $context);

        $this->assertSame('johndoe', $dto->username);
    }

    public function test_length_validation_fails_for_too_short(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        LengthTestDto::from(['username' => 'ab'], $context);
    }

    public function test_length_validation_fails_for_too_long(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        LengthTestDto::from(['username' => str_repeat('a', 21)], $context);
    }

    // =========================================
    // RequiredIf Validation Tests
    // =========================================

    public function test_required_if_validation_passes_when_condition_not_met(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = RequiredIfTestDto::from([
            'subscribe' => false,
            'email' => null,
        ], $context);

        $this->assertFalse($dto->subscribe);
        $this->assertNull($dto->email);
    }

    public function test_required_if_validation_passes_when_condition_met_and_value_provided(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = RequiredIfTestDto::from([
            'subscribe' => true,
            'email' => 'test@example.com',
        ], $context);

        $this->assertTrue($dto->subscribe);
        $this->assertSame('test@example.com', $dto->email);
    }

    public function test_required_if_validation_fails_when_condition_met_and_value_missing(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        RequiredIfTestDto::from([
            'subscribe' => true,
            'email' => null,
        ], $context);
    }

    // =========================================
    // Combined Validators Tests
    // =========================================

    public function test_multiple_validators_on_same_property(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = CombinedValidatorsDto::from([
            'age' => 25,
        ], $context);

        $this->assertSame(25, $dto->age);
    }

    public function test_multiple_validators_fail_min(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        CombinedValidatorsDto::from(['age' => 15], $context);
    }

    public function test_multiple_validators_fail_max(): void
    {
        $context = new Context(validationEnabled: true);

        $this->expectException(HydrationException::class);

        CombinedValidatorsDto::from(['age' => 150], $context);
    }

    // =========================================
    // Complex DTO with Multiple Fields
    // =========================================

    public function test_complex_dto_with_all_validators(): void
    {
        $context = new Context(validationEnabled: true);

        $dto = ComplexValidationDto::from([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'age' => 30,
            'website' => 'https://johndoe.com',
            'username' => 'johndoe',
            'score' => 85,
        ], $context);

        $this->assertSame('John Doe', $dto->name);
        $this->assertSame('john@example.com', $dto->email);
        $this->assertSame(30, $dto->age);
        $this->assertSame('https://johndoe.com', $dto->website);
        $this->assertSame('johndoe', $dto->username);
        $this->assertSame(85, $dto->score);
    }

    // =========================================
    // Validation Disabled Tests
    // =========================================

    public function test_validation_disabled_allows_invalid_data(): void
    {
        // No validation context - validation disabled by default
        $dto = MinTestDto::from(['age' => 5]);

        $this->assertSame(5, $dto->age);
    }

    public function test_validation_explicitly_disabled(): void
    {
        $context = new Context(validationEnabled: false);

        $dto = MinTestDto::from(['age' => 5], $context);

        $this->assertSame(5, $dto->age);
    }
}

// Test DTOs

class MinTestDto extends Dto
{
    public function __construct(
        #[Min(18)]
        public readonly int $age,
    ) {}
}

class MaxTestDto extends Dto
{
    public function __construct(
        #[Max(100)]
        public readonly int $score,
    ) {}
}

class BetweenTestDto extends Dto
{
    public function __construct(
        #[Between(0, 100)]
        public readonly int $percentage,
    ) {}
}

class UrlTestDto extends Dto
{
    public function __construct(
        #[Url]
        public readonly string $website,
    ) {}
}

class NullableUrlTestDto extends Dto
{
    public function __construct(
        #[Url]
        public readonly ?string $website = null,
    ) {}
}

class RegexTestDto extends Dto
{
    public function __construct(
        #[Regex('/^[A-Z]{2}-\d{4}$/')]
        public readonly string $code,
    ) {}
}

class LengthTestDto extends Dto
{
    public function __construct(
        #[Length(min: 3, max: 20)]
        public readonly string $username,
    ) {}
}

class RequiredIfTestDto extends Dto
{
    public function __construct(
        public readonly bool $subscribe,
        #[RequiredIf('subscribe', true)]
        public readonly ?string $email = null,
    ) {}
}

class CombinedValidatorsDto extends Dto
{
    public function __construct(
        #[Required]
        #[Min(18)]
        #[Max(120)]
        public readonly int $age,
    ) {}
}

class ComplexValidationDto extends Dto
{
    public function __construct(
        #[Required]
        #[Length(min: 2, max: 100)]
        public readonly string $name,
        #[Required]
        #[Email]
        public readonly string $email,
        #[Required]
        #[Min(18)]
        #[Max(120)]
        public readonly int $age,
        #[Url]
        public readonly ?string $website = null,
        #[Length(min: 3, max: 20)]
        #[Regex('/^[a-z0-9_]+$/')]
        public readonly ?string $username = null,
        #[Between(0, 100)]
        public readonly ?int $score = null,
    ) {}
}
