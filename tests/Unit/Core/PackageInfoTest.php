<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Core;

use JOOservices\Dto\Core\PackageInfo;
use JOOservices\Dto\Tests\TestCase;

final class PackageInfoTest extends TestCase
{
    public function testNameReturnsPackageName(): void
    {
        self::assertSame('jooservices/dto', PackageInfo::name());
    }

    public function testVersionReturnsDevVersion(): void
    {
        self::assertSame('3.0.0-dev', PackageInfo::version());
    }

    public function testNameConstantMatchesHelper(): void
    {
        self::assertSame(PackageInfo::NAME, PackageInfo::name());
    }

    public function testVersionConstantMatchesHelper(): void
    {
        self::assertSame(PackageInfo::VERSION, PackageInfo::version());
    }
}
