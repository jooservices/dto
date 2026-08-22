<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\TypeParsing;

use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\AddressDto;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use JOOservices\Dto\TypeParsing\DocBlockArrayParser;

final class DocBlockArrayParserTest extends TestCase
{
    public function testReturnsNullWhenDocCommentIsAbsent(): void
    {
        self::assertNull((new DocBlockArrayParser())->arrayItemType(null));
    }

    public function testReturnsNullWhenDocCommentHasNoArrayVarTag(): void
    {
        $doc = '/** Just a description, no @var at all. */';

        self::assertNull((new DocBlockArrayParser())->arrayItemType($doc));
    }

    public function testReturnsNullForNonArrayVarTag(): void
    {
        $doc = '/** @var string */';

        self::assertNull((new DocBlockArrayParser())->arrayItemType($doc));
    }

    public function testParsesBracketSyntaxForBuiltinType(): void
    {
        $doc = '/** @var int[] */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame(TypeDescriptor::KIND_BUILTIN, $type->kind);
        self::assertSame('int', $type->builtin);
        self::assertSame(TypeDescriptor::REQUIRED, $type->nullability);
    }

    public function testParsesBracketSyntaxForClassType(): void
    {
        $doc = '/** @var ' . UserDto::class . '[] */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame(TypeDescriptor::KIND_CLASS, $type->kind);
        self::assertSame(UserDto::class, $type->className);
    }

    public function testParsesGenericArraySyntax(): void
    {
        $doc = '/** @var array<' . UserDto::class . '> */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame(TypeDescriptor::KIND_CLASS, $type->kind);
        self::assertSame(UserDto::class, $type->className);
    }

    public function testParsesGenericListSyntax(): void
    {
        $doc = '/** @var list<string> */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame(TypeDescriptor::KIND_BUILTIN, $type->kind);
        self::assertSame('string', $type->builtin);
    }

    public function testMixedItemTypeIsNullable(): void
    {
        $doc = '/** @var mixed[] */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame(TypeDescriptor::KIND_MIXED, $type->kind);
        self::assertTrue($type->allowsNull());
    }

    public function testStripsLeadingBackslashFromFullyQualifiedClassNames(): void
    {
        $doc = '/** @var \\' . UserDto::class . '[] */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame(UserDto::class, $type->className);
    }

    public function testGenericSyntaxTakesPriorityOverBracketSyntaxWhenBothCouldMatch(): void
    {
        $doc = '/** @var array<int> */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc);

        self::assertNotNull($type);
        self::assertSame('int', $type->builtin);
    }

    public function testResolvesRelativeClassNameUsingDeclaringClassNamespace(): void
    {
        $doc = '/** @var list<AddressDto> */';

        $type = (new DocBlockArrayParser())->arrayItemType($doc, UserDto::class);

        self::assertNotNull($type);
        self::assertSame(AddressDto::class, $type->className);
    }
}
