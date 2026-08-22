<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\Optional;
use JOOservices\Dto\Tests\TestCase;
use RuntimeException;

final class OptionalTest extends TestCase
{
    /**
     * @throws RuntimeException
     */
    public function testPresentOptionalMapsFiltersAndFallbacks(): void
    {
        $optional = Optional::fromValue('value');

        self::assertSame('value', $optional->get());
        self::assertSame('value', $optional->orElse('fallback'));
        self::assertSame('value', $optional->orElseGet(static fn(): string => 'fallback'));
        self::assertSame(
            'value',
            $optional->orElseThrow(static fn(): RuntimeException => new RuntimeException('fallback')),
        );

        $seen = null;
        $optional->ifPresent(static function (string $present) use (&$seen): void {
            $seen = $present;
        });
        self::assertSame('value', $seen);

        $mapped = $optional->map(static fn(string $present): int => strlen($present));
        self::assertTrue($mapped->isPresent());
        self::assertSame(5, $mapped->get());

        $filtered = $optional->filter(static fn(string $present): bool => $present !== '');
        self::assertTrue($filtered->isPresent());
    }

    public function testEmptyOptionalRejectsGetAndUsesFallbacks(): void
    {
        $empty = Optional::empty();

        self::assertTrue($empty->isEmpty());
        self::assertSame('fallback', $empty->orElse('fallback'));
        self::assertSame('generated', $empty->orElseGet(static fn(): string => 'generated'));

        $empty->ifEmpty(static function (): void {
        });

        try {
            $empty->get();
            self::fail('Expected RuntimeException');
        } catch (RuntimeException) {
        }

        try {
            $empty->orElseThrow(static fn(): RuntimeException => new RuntimeException('fallback'));
            self::fail('Expected RuntimeException');
        } catch (RuntimeException) {
        }

        self::assertTrue($empty->map(static fn(mixed $present): mixed => $present)->isEmpty());
    }
}
