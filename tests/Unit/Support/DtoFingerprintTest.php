<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Support;

use JOOservices\Dto\Support\DtoFingerprint;
use JOOservices\Dto\Tests\Fixtures\MetaBagDto;
use JOOservices\Dto\Tests\TestCase;
use JsonException;

final class DtoFingerprintTest extends TestCase
{
    /**
     * @throws JsonException
     */
    public function testHashIsStableWhenNestedAssociativeArrayKeysDifferInOrder(): void
    {
        $left = new MetaBagDto(['b' => 1, 'a' => 2]);
        $right = new MetaBagDto(['a' => 2, 'b' => 1]);

        $fingerprint = new DtoFingerprint();

        self::assertSame($fingerprint->hash($left), $fingerprint->hash($right));
    }
}
