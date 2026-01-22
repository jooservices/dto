<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\DefaultFrom;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class DefaultFromTest extends TestCase
{
    public function test_constructor_with_config(): void
    {
        $attr = new DefaultFrom(config: 'app.timezone');

        $this->assertSame('app.timezone', $attr->config);
        $this->assertNull($attr->env);
        $this->assertNull($attr->method);
    }

    public function test_constructor_with_env(): void
    {
        $attr = new DefaultFrom(env: 'API_KEY');

        $this->assertNull($attr->config);
        $this->assertSame('API_KEY', $attr->env);
        $this->assertNull($attr->method);
    }

    public function test_constructor_with_method(): void
    {
        $attr = new DefaultFrom(method: 'getDefaultValue');

        $this->assertNull($attr->config);
        $this->assertNull($attr->env);
        $this->assertSame('getDefaultValue', $attr->method);
    }

    public function test_constructor_with_multiple_sources(): void
    {
        $attr = new DefaultFrom(
            config: 'app.timezone',
            env: 'TZ',
            method: 'getDefault',
        );

        $this->assertSame('app.timezone', $attr->config);
        $this->assertSame('TZ', $attr->env);
        $this->assertSame('getDefault', $attr->method);
    }

    public function test_constructor_throws_with_no_sources(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Must specify at least one of: config, env, method');

        new DefaultFrom;
    }

    public function test_constructor_validates_method_name(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid method name');

        new DefaultFrom(method: 'shell_exec()');
    }

    public function test_constructor_rejects_method_with_special_characters(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DefaultFrom(method: 'some-method');
    }

    public function test_constructor_rejects_method_starting_with_number(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new DefaultFrom(method: '123method');
    }

    public function test_constructor_allows_valid_method_names(): void
    {
        $valid = [
            'getDefault',
            'get_default',
            '_private',
            'Method123',
            'CONSTANT_STYLE',
        ];

        foreach ($valid as $methodName) {
            $attr = new DefaultFrom(method: $methodName);
            $this->assertSame($methodName, $attr->method);
        }
    }

    public function test_readonly_properties(): void
    {
        $attr = new DefaultFrom(config: 'test');

        $reflection = new ReflectionClass($attr);
        $this->assertTrue($reflection->isReadOnly());
    }
}
