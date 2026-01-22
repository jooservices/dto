<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Pipeline;

use JOOservices\Dto\Hydration\Pipeline\TrimStrings;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use PHPUnit\Framework\TestCase;

final class TrimStringsTest extends TestCase
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

    public function test_trims_whitespace(): void
    {
        $step = new TrimStrings;

        $result = $step->process('  hello world  ', $this->property, null);

        $this->assertSame('hello world', $result);
    }

    public function test_trims_newlines(): void
    {
        $step = new TrimStrings;

        $result = $step->process("\n\nhello\n\n", $this->property, null);

        $this->assertSame('hello', $result);
    }

    public function test_trims_tabs_and_other_whitespace(): void
    {
        $step = new TrimStrings;

        $result = $step->process("\t\r\v\0hello\t\r\v\0", $this->property, null);

        $this->assertSame('hello', $result);
    }

    public function test_custom_characters(): void
    {
        $step = new TrimStrings(characters: 'x');

        $result = $step->process('xxxhelloxxx', $this->property, null);

        $this->assertSame('hello', $result);
    }

    public function test_ignores_non_strings(): void
    {
        $step = new TrimStrings;

        $this->assertSame(123, $step->process(123, $this->property, null));
        $this->assertSame(45.6, $step->process(45.6, $this->property, null));
        $this->assertTrue($step->process(true, $this->property, null));
        $this->assertSame(['a'], $step->process(['a'], $this->property, null));
        $this->assertNull($step->process(null, $this->property, null));
    }

    public function test_empty_string(): void
    {
        $step = new TrimStrings;

        $result = $step->process('', $this->property, null);

        $this->assertSame('', $result);
    }

    public function test_only_whitespace(): void
    {
        $step = new TrimStrings;

        $result = $step->process('   ', $this->property, null);

        $this->assertSame('', $result);
    }
}
