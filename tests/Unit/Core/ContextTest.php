<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use InvalidArgumentException;
use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SerializationOptions;
use JOOservices\Dto\Tests\TestCase;

final class ContextTest extends TestCase
{
    /**
     * @throws InvalidArgumentException
     */
    public function testPermissiveContextDefaults(): void
    {
        $ctx = Context::permissive();

        self::assertSame(CastMode::Permissive->value, $ctx->castMode);
        self::assertFalse($ctx->shouldRejectUnknownKeys());
        self::assertFalse($ctx->shouldDisallowScalarCoercion());
    }

    /**
     * @throws InvalidArgumentException
     */
    public function testStrictContextCanBeReconfigured(): void
    {
        $wrap = 'payload';
        $ctx = Context::strict()
            ->withNamingStrategy(null)
            ->withValidationEnabled(true)
            ->withSerializationOptions(new SerializationOptions(wrap: $wrap))
            ->withCustomData(['trace' => true])
            ->withCastMode(CastMode::Loose)
            ->withGlobalPipeline([])
            ->withRejectUnknownKeys(false)
            ->withDisallowScalarCoercion(true)
            ->withSourceNamingOnOutput(true);

        self::assertTrue($ctx->validationEnabled);
        self::assertSame(['trace' => true], $ctx->customData);
        self::assertFalse($ctx->shouldRejectUnknownKeys());
        self::assertTrue($ctx->shouldDisallowScalarCoercion());
        self::assertSame($wrap, $ctx->serializationOptionsOrDefault()->wrap);
    }

    public function testSerializationOptionsFiltersAndDepth(): void
    {
        $wrap = 'envelope';
        $options = (new SerializationOptions())
            ->withOnly(['name'])
            ->withExcept(['secret'])
            ->withMaxDepth(3)
            ->withIncludeLazy([])
            ->withWrap($wrap);

        self::assertTrue($options->shouldInclude('anything'));
        self::assertTrue($options->shouldIncludeLazy('lazy'));
        self::assertTrue($options->canDescend(0));
        self::assertFalse($options->canDescend(3));
    }
}
