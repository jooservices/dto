<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizers\JsonInputNormalizer;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @group integration
 */
#[Group('integration')]
final class JsonInputNormalizerTest extends TestCase
{
    public function testSupportsOnlyStrings(): void
    {
        $normalizer = new JsonInputNormalizer();

        self::assertTrue($normalizer->supports('{"a":1}'));
        self::assertFalse($normalizer->supports(['a' => 1]));
        self::assertFalse($normalizer->supports(new stdClass()));
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeDecodesAJsonObjectIntoAnArray(): void
    {
        $normalizer = new JsonInputNormalizer();

        self::assertSame(
            ['name' => 'Ada', 'age' => 30],
            $normalizer->normalize('{"name":"Ada","age":30}'),
        );
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeKeepsBigintAsString(): void
    {
        $decoded = (new JsonInputNormalizer())->normalize('{"value":9223372036854775808}');

        self::assertSame('9223372036854775808', $decoded['value']);
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeRejectsInvalidJson(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Invalid JSON input.');

        (new JsonInputNormalizer())->normalize('{not json');
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeRejectsJsonThatDoesNotDecodeToAnArray(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('JSON input must decode to an object/array.');

        (new JsonInputNormalizer())->normalize('"just a string"');
    }

    /**
     * Defensive branch: calling normalize() directly with a non-string bypasses supports().
     *
     * @throws HydrationException
     */
    public function testNormalizeRejectsANonStringValue(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('JSON input must be a string.');

        (new JsonInputNormalizer())->normalize(123);
    }
}
