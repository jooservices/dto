<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizers\ArrayInputNormalizer;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @group integration
 */
#[Group('integration')]
final class ArrayInputNormalizerTest extends TestCase
{
    public function testSupportsOnlyArrays(): void
    {
        $normalizer = new ArrayInputNormalizer();

        self::assertTrue($normalizer->supports(['a' => 1]));
        self::assertFalse($normalizer->supports('a string'));
        self::assertFalse($normalizer->supports(new stdClass()));
        self::assertFalse($normalizer->supports(null));
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeReturnsTheArrayUnchanged(): void
    {
        $normalizer = new ArrayInputNormalizer();
        $source = ['name' => 'Ada', 'age' => 30];

        self::assertSame($source, $normalizer->normalize($source));
    }
}
