<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use Exception;
use JOOservices\Dto\Core\Optional;
use LogicException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use RuntimeException;
use stdClass;

final class OptionalTest extends TestCase
{
    public function test_of_creates_optional_with_value(): void
    {
        $optional = Optional::of('test');

        $this->assertTrue($optional->isPresent());
        $this->assertFalse($optional->isEmpty());
        $this->assertSame('test', $optional->get());
    }

    public function test_empty_creates_empty_optional(): void
    {
        $optional = Optional::empty();

        $this->assertFalse($optional->isPresent());
        $this->assertTrue($optional->isEmpty());
    }

    public function test_get_throws_on_empty(): void
    {
        $optional = Optional::empty();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Optional value not present');

        $optional->get();
    }

    public function test_or_else_returns_value_when_present(): void
    {
        $optional = Optional::of('value');

        $this->assertSame('value', $optional->orElse('default'));
    }

    public function test_or_else_returns_default_when_empty(): void
    {
        $optional = Optional::empty();

        $this->assertSame('default', $optional->orElse('default'));
    }

    public function test_or_else_get_returns_value_when_present(): void
    {
        $optional = Optional::of('value');

        $result = $optional->orElseGet(fn () => 'computed');

        $this->assertSame('value', $result);
    }

    public function test_or_else_get_calls_supplier_when_empty(): void
    {
        $optional = Optional::empty();
        $called = false;

        $result = $optional->orElseGet(function () use (&$called) {
            $called = true;

            return 'computed';
        });

        $this->assertTrue($called);
        $this->assertSame('computed', $result);
    }

    public function test_or_else_throw_returns_value_when_present(): void
    {
        $optional = Optional::of('value');

        $result = $optional->orElseThrow(fn () => new Exception('Error'));

        $this->assertSame('value', $result);
    }

    public function test_or_else_throw_throws_when_empty(): void
    {
        $optional = Optional::empty();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('No value');

        $optional->orElseThrow(fn () => new LogicException('No value'));
    }

    public function test_if_present_executes_callback_when_present(): void
    {
        $optional = Optional::of('value');
        $result = null;

        $optional->ifPresent(function ($value) use (&$result) {
            $result = $value;
        });

        $this->assertSame('value', $result);
    }

    public function test_if_present_does_nothing_when_empty(): void
    {
        $optional = Optional::empty();
        $called = false;

        $optional->ifPresent(function () use (&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }

    public function test_if_empty_executes_callback_when_empty(): void
    {
        $optional = Optional::empty();
        $called = false;

        $optional->ifEmpty(function () use (&$called) {
            $called = true;
        });

        $this->assertTrue($called);
    }

    public function test_if_empty_does_nothing_when_present(): void
    {
        $optional = Optional::of('value');
        $called = false;

        $optional->ifEmpty(function () use (&$called) {
            $called = true;
        });

        $this->assertFalse($called);
    }

    public function test_map_transforms_value_when_present(): void
    {
        $optional = Optional::of(5);

        $result = $optional->map(fn ($x) => $x * 2);

        $this->assertTrue($result->isPresent());
        $this->assertSame(10, $result->get());
    }

    public function test_map_returns_empty_when_empty(): void
    {
        $optional = Optional::empty();

        $result = $optional->map(
            /** @param mixed $x */
            fn ($x) => is_int($x) ? $x * 2 : 0,
        );

        $this->assertTrue($result->isEmpty());
    }

    public function test_filter_keeps_value_when_predicate_true(): void
    {
        $optional = Optional::of(10);

        $result = $optional->filter(fn ($x) => $x > 5);

        $this->assertTrue($result->isPresent());
        $this->assertSame(10, $result->get());
    }

    public function test_filter_returns_empty_when_predicate_false(): void
    {
        $optional = Optional::of(3);

        $result = $optional->filter(fn ($x) => $x > 5);

        $this->assertTrue($result->isEmpty());
    }

    public function test_filter_returns_empty_when_empty(): void
    {
        $optional = Optional::empty();

        $result = $optional->filter(fn ($x) => true);

        $this->assertTrue($result->isEmpty());
    }

    public function test_chaining(): void
    {
        $optional = Optional::of(5)
            ->map(fn ($x) => $x * 2)
            ->filter(fn ($x) => $x > 5)
            ->map(fn ($x) => (string) $x);

        $this->assertSame('10', $optional->get());
    }

    public function test_with_different_types(): void
    {
        $stringOpt = Optional::of('text');
        $intOpt = Optional::of(42);
        $arrayOpt = Optional::of([1, 2, 3]);
        $objectOpt = Optional::of(new stdClass);

        $this->assertSame('text', $stringOpt->get());
        $this->assertSame(42, $intOpt->get());
        $this->assertSame([1, 2, 3], $arrayOpt->get());
        $this->assertInstanceOf(stdClass::class, $objectOpt->get());
    }

    public function test_readonly_class(): void
    {
        $reflection = new ReflectionClass(Optional::class);
        $this->assertTrue($reflection->isFinal());
        $this->assertTrue($reflection->isReadOnly());
    }
}
