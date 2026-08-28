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

    public function testVersionReturnsReleaseVersion(): void
    {
        self::assertSame('3.2.0', PackageInfo::version());
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
