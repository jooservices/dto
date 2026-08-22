<?php

declare(strict_types=1);

namespace JOOservices\Dto\Tests\Unit\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Core\SharedDefaults;
use JOOservices\Dto\Engine\CasterBootstrap;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Exceptions\HydrationException;
use JOOservices\Dto\Exceptions\MappingException;
use JOOservices\Dto\Exceptions\ValidationException;
use JOOservices\Dto\Hydration\Pipeline\TrimStrings;
use JOOservices\Dto\Hydration\Pipeline\Uppercase;
use JOOservices\Dto\Hydration\PipelineRunner;
use JOOservices\Dto\Hydration\PropertyValuePreparer;
use JOOservices\Dto\Meta\TypeDescriptor;
use JOOservices\Dto\Tests\Support\PropertyMetaBuilder;
use JOOservices\Dto\Tests\TestCase;
use ReflectionException;

final class PropertyValuePreparerTest extends TestCase
{
    private function preparer(): PropertyValuePreparer
    {
        return new PropertyValuePreparer(new PipelineRunner(), (new CasterBootstrap())->create(new SharedDefaults()));
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testGlobalPipelineRunsBeforePropertyPipeline(): void
    {
        $property = PropertyMetaBuilder::make(
            name: 'value',
            type: TypeDescriptor::builtin('string'),
            attributes: [new Pipeline([TrimStrings::class])],
        );
        $ctx = (new Context(false, false))->withGlobalPipeline([Uppercase::class]);

        $result = $this->preparer()->prepare($property, '  abc  ', $ctx);

        // Global uppercase runs first (spaces survive uppercasing), then the
        // property-level trim strips them -- proving the execution order.
        self::assertSame('ABC', $result);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPrepareCastsAfterPipelinesHaveRun(): void
    {
        $property = PropertyMetaBuilder::make(
            name: 'value',
            type: TypeDescriptor::builtin('int'),
        );

        $result = $this->preparer()->prepare($property, '42', new Context(false, false));

        self::assertSame(42, $result);
    }

    /**
     * @throws CastException
     * @throws HydrationException
     * @throws InvalidArgumentException
     * @throws MappingException
     * @throws ReflectionException
     * @throws ValidationException
     */
    public function testPrepareWithoutGlobalPipelineOnlyAppliesThePropertyPipeline(): void
    {
        $property = PropertyMetaBuilder::make(
            name: 'value',
            type: TypeDescriptor::builtin('string'),
            attributes: [new Pipeline([TrimStrings::class])],
        );

        $result = $this->preparer()->prepare($property, '  hi  ', new Context(false, false));

        self::assertSame('hi', $result);
    }
}
