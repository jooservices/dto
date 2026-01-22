<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\Pipeline;

use JOOservices\Dto\Hydration\Pipeline\StripTags;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use PHPUnit\Framework\TestCase;

final class StripTagsTest extends TestCase
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

    public function test_strips_tags(): void
    {
        $step = new StripTags;

        $result = $step->process('<p>Hello <b>World</b></p>', $this->property, null);

        $this->assertSame('Hello World', $result);
    }

    public function test_strips_script_tags(): void
    {
        $step = new StripTags;

        $result = $step->process('<script>alert("xss")</script>Safe', $this->property, null);

        $this->assertSame('alert("xss")Safe', $result);
    }

    public function test_allows_specific_tags(): void
    {
        $step = new StripTags(allowedTags: '<p><b>');

        $result = $step->process('<p>Hello <b>World</b> <script>bad</script></p>', $this->property, null);

        $this->assertSame('<p>Hello <b>World</b> bad</p>', $result);
    }

    public function test_ignores_non_strings(): void
    {
        $step = new StripTags;

        $this->assertSame(123, $step->process(123, $this->property, null));
        $this->assertSame(['a'], $step->process(['a'], $this->property, null));
        $this->assertNull($step->process(null, $this->property, null));
    }

    public function test_empty_string(): void
    {
        $step = new StripTags;

        $result = $step->process('', $this->property, null);

        $this->assertSame('', $result);
    }

    public function test_no_tags(): void
    {
        $step = new StripTags;

        $result = $step->process('Plain text', $this->property, null);

        $this->assertSame('Plain text', $result);
    }
}
