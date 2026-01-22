<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Pipeline;

use JOOservices\Dto\Hydration\Pipeline\Uppercase;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use PHPUnit\Framework\TestCase;

final class UppercaseTest extends TestCase
{
    private PropertyMeta $property;

    protected function setUp(): void
    {
        parent::setUp();

        $this->property = new PropertyMeta(
            name: 'test',
            type: new TypeDescriptor(
                name: 'string',
                isBuiltin: true,
                isNullable: false,
                isArray: false,
                arrayItemType: null,
                isEnum: false,
                enumClass: null,
                isDto: false,
                isDateTime: false,
            ),
            isReadonly: false,
            hasDefault: false,
            defaultValue: null,
            mapFrom: null,
            casterClass: null,
            transformerClass: null,
            isHidden: false,
            validationRules: [],
            attributes: [],
        );
    }

    public function test_converts_to_uppercase(): void
    {
        $step = new Uppercase;

        $result = $step->process('hello world', $this->property, null);

        $this->assertSame('HELLO WORLD', $result);
    }

    public function test_handles_mixed_case(): void
    {
        $step = new Uppercase;

        $result = $step->process('HeLLo WoRLd', $this->property, null);

        $this->assertSame('HELLO WORLD', $result);
    }

    public function test_ignores_non_strings(): void
    {
        $step = new Uppercase;

        $this->assertSame(123, $step->process(123, $this->property, null));
        $this->assertSame(['a'], $step->process(['a'], $this->property, null));
        $this->assertNull($step->process(null, $this->property, null));
    }

    public function test_empty_string(): void
    {
        $step = new Uppercase;

        $result = $step->process('', $this->property, null);

        $this->assertSame('', $result);
    }

    public function test_already_uppercase(): void
    {
        $step = new Uppercase;

        $result = $step->process('HELLO', $this->property, null);

        $this->assertSame('HELLO', $result);
    }
}
