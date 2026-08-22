<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Integration\Attributes;

use InvalidArgumentException;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\Pipeline\TrimStrings;
use JOOservices\Dto\Tests\Fixtures\AttrFixturePipeline;
use JOOservices\Dto\Tests\Fixtures\AttrFixturePipelineWithOptions;
use JOOservices\Dto\Tests\Fixtures\UserDto;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

/**
 * Real hydration coverage for the Pipeline attribute, in both step forms:
 * `class-string` and `[class-string, options]`.
 */
final class PipelineAttributeTest extends TestCase
{
    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPlainClassStringStepsRunInOrder(): void
    {
        $dto = AttrFixturePipeline::fromArray(['code' => '  abc  ']);

        self::assertSame('ABC', $dto->code);
    }

    /**
     * The `[class-string, options]` tuple form spreads options into the step's constructor.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testArrayTupleStepFormSpreadsOptionsIntoTheStepConstructor(): void
    {
        $dto = AttrFixturePipelineWithOptions::fromArray([
            'html' => '<b>bold</b><i>italic</i>',
        ]);

        self::assertSame('<b>bold</b>italic', $dto->html);
    }

    /**
     * The global pipeline (Context::$globalPipeline) applies to every property, not just ones
     * carrying a Pipeline attribute, and skips non-string values transparently.
     *
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testGlobalPipelineAppliesToEveryPropertyRegardlessOfAttribute(): void
    {
        $ctx = new Context(validationEnabled: false, sourceKeyOut: false, globalPipeline: [TrimStrings::class]);

        $dto = UserDto::fromArray(['name' => '  Ada  ', 'age' => 30], $ctx);

        self::assertSame('Ada', $dto->name);
        self::assertSame(30, $dto->age);
    }
}
