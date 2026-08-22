<?php

/**
 *  integration
 */

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration\InputNormalizers;

use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Hydration\InputNormalizers\ArrayInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\ChainInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\JsonInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\ObjectInputNormalizer;
use JOOservices\Dto\Tests\TestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @group integration
 */
#[Group('integration')]
final class ChainInputNormalizerTest extends TestCase
{
    private function chain(): ChainInputNormalizer
    {
        return new ChainInputNormalizer([
            new ArrayInputNormalizer(),
            new JsonInputNormalizer(),
            new ObjectInputNormalizer(),
        ]);
    }

    public function testSupportsDelegatesToTheFirstMatchingNormalizer(): void
    {
        $chain = $this->chain();

        self::assertTrue($chain->supports(['a' => 1]));
        self::assertTrue($chain->supports('{"a":1}'));
        self::assertTrue($chain->supports(new stdClass()));
    }

    public function testSupportsIsFalseWhenNoNormalizerMatches(): void
    {
        $chain = new ChainInputNormalizer([new ArrayInputNormalizer()]);

        self::assertFalse($chain->supports('a string'));
    }

    /**
     * @throws HydrationException
     */
    public function testNormalizeUsesTheFirstNormalizerThatSupportsTheSource(): void
    {
        $chain = $this->chain();

        self::assertSame(['a' => 1], $chain->normalize(['a' => 1]));
        self::assertSame(['a' => 1], $chain->normalize('{"a":1}'));
    }

    /**
     * Unhappy: with an empty chain (or one where nothing supports the source), normalize()
     * raises a clear HydrationException rather than silently returning an empty array.
     *
     * @throws HydrationException
     */
    public function testNormalizeRaisesHydrationExceptionWhenNothingSupportsTheSource(): void
    {
        $chain = new ChainInputNormalizer([new ArrayInputNormalizer()]);

        $this->expectException(HydrationException::class);
        $this->expectExceptionMessage('Unsupported input type for hydration.');

        $chain->normalize('a string');
    }

    /**
     * @throws HydrationException
     */
    public function testEmptyChainNeverSupportsAnything(): void
    {
        $chain = new ChainInputNormalizer([]);

        self::assertFalse($chain->supports(['a' => 1]));

        $this->expectException(HydrationException::class);
        $chain->normalize(['a' => 1]);
    }
}
