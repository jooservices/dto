<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Hydration\UnknownKeyGuard;
use JOOservices\Dto\Meta\ClassMeta;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;

final class UnknownKeyGuardTest extends TestCase
{
    private function meta(): ClassMeta
    {
        $property = PropertyMetaBuilder::make(
            name: 'name',
            type: TypeDescriptor::builtin('string'),
        );

        return new ClassMeta(
            className: UserDto::class,
            properties: [$property],
            ctorParams: ['name'],
            hasConstructor: true,
        );
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testAssertKnownIsANoOpWhenRejectionIsNotRequested(): void
    {
        $guard = new UnknownKeyGuard(new Mapper());

        $guard->assertKnown(['name' => 'x', 'bogus' => true], $this->meta(), new Context(false, false));

        self::addToAssertionCount(1);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testAssertKnownThrowsForUnexpectedKeyUnderStrictContext(): void
    {
        $guard = new UnknownKeyGuard(new Mapper());

        $this->expectException(MappingException::class);
        $this->expectExceptionMessage('Unexpected input key.');

        $guard->assertKnown(['name' => 'x', 'bogus' => true], $this->meta(), Context::strict());
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testAssertKnownHonoursExplicitRejectUnknownKeysOverride(): void
    {
        $guard = new UnknownKeyGuard(new Mapper());
        $ctx = (new Context(false, false))->withRejectUnknownKeys(true);

        $this->expectException(MappingException::class);

        $guard->assertKnown(['name' => 'x', 'bogus' => true], $this->meta(), $ctx);
    }

    /**
     * @throws InvalidArgumentException
     * @throws MappingException
     */
    public function testAssertKnownPassesWhenAllKeysAreExpected(): void
    {
        $guard = new UnknownKeyGuard(new Mapper());

        $guard->assertKnown(['name' => 'x'], $this->meta(), Context::strict());

        self::addToAssertionCount(1);
    }
}
