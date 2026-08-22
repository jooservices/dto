<?php

declare(strict_types=1);

namespace JOOservices\Dto\Hydration;

use InvalidArgumentException;
use JOOservices\Dto\Attributes\Pipeline;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Support\CachedOptionedInstantiator;

final class PipelineRunner
{
    public function __construct(
        private readonly CachedOptionedInstantiator $instantiator = new CachedOptionedInstantiator(),
    ) {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function applyGlobal(mixed $value, ?Context $ctx): mixed
    {
        if ($ctx === null) {
            return $value;
        }

        foreach ($ctx->globalPipeline as $step) {
            $value = $this->runStep($step, [], $value);
        }

        return $value;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function applyProperty(mixed $value, Pipeline $pipeline): mixed
    {
        foreach ($pipeline->steps as $step) {
            if (is_array($step)) {
                $class = $step[0];
                /** @var array<int|string, mixed> $options */
                $options = $step[1] ?? [];
                $value = $this->runStep($class, $options, $value);
                continue;
            }

            $value = $this->runStep($step, [], $value);
        }

        return $value;
    }

    /**
     * @param  class-string  $className
     * @param  array<int|string, mixed>  $options
     *
     * @throws InvalidArgumentException
     */
    private function runStep(string $className, array $options, mixed $value): mixed
    {
        $instance = $this->instantiator->make($className, $options);
        if (! $instance instanceof PipelineStepInterface) {
            throw new InvalidArgumentException('Pipeline step must implement PipelineStepInterface.');
        }

        return $instance->handle($value);
    }
}
