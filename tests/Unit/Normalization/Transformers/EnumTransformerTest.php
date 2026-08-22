<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization\Transformers;

use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Tests\Fixtures\CoreFixturePureEnum;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @group integration
 */
#[Group('integration')]
final class EnumTransformerTest extends TestCase
{
    public function testSupportsAnyUnitEnum(): void
    {
        $transformer = new EnumTransformer();

        self::assertTrue($transformer->supports(Status::Active));
        self::assertTrue($transformer->supports(CoreFixturePureEnum::One));
        self::assertFalse($transformer->supports('active'));
    }

    public function testTransformReturnsBackedValueForBackedEnums(): void
    {
        $transformer = new EnumTransformer();

        self::assertSame('active', $transformer->transform(Status::Active));
        self::assertSame('inactive', $transformer->transform(Status::Inactive));
    }

    public function testTransformReturnsCaseNameForPureEnums(): void
    {
        $transformer = new EnumTransformer();

        self::assertSame('One', $transformer->transform(CoreFixturePureEnum::One));
        self::assertSame('Two', $transformer->transform(CoreFixturePureEnum::Two));
    }

    public function testTransformPassesThroughNonEnumValuesUnchanged(): void
    {
        $transformer = new EnumTransformer();

        self::assertSame(42, $transformer->transform(42));
    }
}
