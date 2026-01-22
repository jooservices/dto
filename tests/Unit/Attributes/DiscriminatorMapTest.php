<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes;

use JOOservices\Dto\Attributes\DiscriminatorMap;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DiscriminatorMapTest extends TestCase
{
    public function test_constructor_with_field_discriminator(): void
    {
        $map = [
            'card' => 'CreditCardDto',
            'paypal' => 'PayPalDto',
        ];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: 'type',
            map: $map,
        );

        $this->assertSame('type', $attr->discriminator);
        $this->assertSame($map, $attr->map);
    }

    public function test_constructor_with_callable_discriminator(): void
    {
        $callable = static fn (array $data) => $data['payment_type'] ?? 'default';
        $map = ['card' => 'CreditCardDto'];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: $callable,
            map: $map,
        );

        $this->assertTrue(is_callable($attr->discriminator));
        $this->assertSame($map, $attr->map);
    }

    public function test_resolve_type_with_field_discriminator(): void
    {
        $map = [
            'card' => 'CreditCardDto',
            'paypal' => 'PayPalDto',
        ];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: 'type',
            map: $map,
        );

        $this->assertSame('CreditCardDto', $attr->resolveType(['type' => 'card']));
        $this->assertSame('PayPalDto', $attr->resolveType(['type' => 'paypal']));
    }

    public function test_resolve_type_returns_null_for_unknown_value(): void
    {
        $map = ['card' => 'CreditCardDto'];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: 'type',
            map: $map,
        );

        $this->assertNull($attr->resolveType(['type' => 'unknown']));
    }

    public function test_resolve_type_returns_null_when_field_missing(): void
    {
        $map = ['card' => 'CreditCardDto'];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: 'type',
            map: $map,
        );

        $this->assertNull($attr->resolveType([]));
    }

    public function test_resolve_type_with_callable_discriminator(): void
    {
        $map = [
            'card' => 'CreditCardDto',
            'default' => 'GenericPaymentDto',
        ];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: static fn (array $data) => $data['payment_type'] ?? $data['type'] ?? 'default',
            map: $map,
        );

        $this->assertSame('CreditCardDto', $attr->resolveType(['payment_type' => 'card']));
        $this->assertSame('CreditCardDto', $attr->resolveType(['type' => 'card']));
        $this->assertSame('GenericPaymentDto', $attr->resolveType([]));
    }

    public function test_resolve_type_with_complex_callable(): void
    {
        $map = [
            'card' => 'CreditCardDto',
            'paypal' => 'PayPalDto',
            'default' => 'GenericPaymentDto',
        ];

        // @phpstan-ignore argument.type
        $attr = new DiscriminatorMap(
            discriminator: static function (array $data) {
                if (isset($data['credit_card'])) {
                    return 'card';
                }

                if (isset($data['paypal_email'])) {
                    return 'paypal';
                }

                return 'default';
            },
            map: $map,
        );

        $this->assertSame('CreditCardDto', $attr->resolveType(['credit_card' => '1234']));
        $this->assertSame('PayPalDto', $attr->resolveType(['paypal_email' => 'test@example.com']));
        $this->assertSame('GenericPaymentDto', $attr->resolveType([]));
    }

    public function test_readonly_class(): void
    {
        $reflection = new ReflectionClass(DiscriminatorMap::class);
        $this->assertTrue($reflection->isReadOnly());
    }
}
