<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Meta;

use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;

final class MemoryMetaCacheLruTest extends TestCase
{
    public function testC18GetBumpsRecencyForLruEviction(): void
    {
        $cache = new MemoryMetaCache(2);
        $a = $this->meta('A');
        $b = $this->meta('B');
        $c = $this->meta('C');

        $cache->set('A', $a);
        $cache->set('B', $b);
        self::assertNotNull($cache->get('A'));
        $cache->set('C', $c);

        self::assertTrue($cache->has('A'));
        self::assertFalse($cache->has('B'));
        self::assertTrue($cache->has('C'));
    }

    private function meta(string $name): ClassMeta
    {
        unset($name);

        return new ClassMeta(
            className: UserDto::class,
            properties: [
                new PropertyMeta(
                    name: 'id',
                    type: TypeDescriptor::builtin('int'),
                    allowsNull: false,
                    hasDefault: false,
                ),
            ],
            ctorParams: ['id'],
            hasConstructor: true,
        );
    }
}
