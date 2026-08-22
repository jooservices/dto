<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizers\ObjectInputNormalizer;
use JOOservices\Dto\Tests\Support\MixedVisibilityObject;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @group integration
 */
#[Group('integration')]
final class ObjectInputNormalizerTest extends TestCase
{
    public function testSupportsOnlyObjects(): void
    {
        $normalizer = new ObjectInputNormalizer();

        self::assertTrue($normalizer->supports(new stdClass()));
        self::assertFalse($normalizer->supports(['a' => 1]));
        self::assertFalse($normalizer->supports('a string'));
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeExtractsPublicPropertiesOnly(): void
    {
        $normalizer = new ObjectInputNormalizer();

        self::assertSame(['name' => 'Ada', 'age' => 30], $normalizer->normalize(new MixedVisibilityObject()));
    }

    /**
     * Defensive branch: calling normalize() directly with a non-object bypasses supports().
     *
     * @throws HydrationException
     */
    public function testNormalizeRejectsANonObjectValue(): void
    {
        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Object input expected.');

        (new ObjectInputNormalizer())->normalize('not an object');
    }
}
