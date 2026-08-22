<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Normalization;

use DateTimeImmutable;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Tests\Fixtures\CoreFixtureUppercaseTransformer;
use JOOservices\Dto\Tests\Fixtures\Status;
use JOOservices\Dto\Tests\TestCase;

final class TransformerRegistryTest extends TestCase
{
    public function testTransformDelegatesToTheFirstSupportingTransformer(): void
    {
        $registry = new TransformerRegistry();
        $registry->register(new DateTimeTransformer());
        $registry->register(new EnumTransformer());

        $date = new DateTimeImmutable('2026-01-01T00:00:00+00:00');

        self::assertSame('active', $registry->transform(Status::Active));
        self::assertSame('2026-01-01T00:00:00+00:00', $registry->transform($date));
    }

    public function testTransformReturnsValueUnchangedWhenNothingMatches(): void
    {
        $registry = new TransformerRegistry();

        self::assertSame('untouched', $registry->transform('untouched'));
    }

    public function testFirstRegisteredMatchingTransformerWinsOverLaterOnes(): void
    {
        $registry = new TransformerRegistry();
        $registry->register(new CoreFixtureUppercaseTransformer('first-'));
        $registry->register(new CoreFixtureUppercaseTransformer('second-'));

        self::assertSame('first-X', $registry->transform('x'));
    }
}
