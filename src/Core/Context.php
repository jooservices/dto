<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use JOOservices\Dto\Hydration\Naming\NamingStrategyInterface;

final readonly class Context
{
    private const string TRANSFORMER_MODE_FULL = 'full';

    private const string CAST_MODE_LOOSE = 'loose';

    private const string CAST_MODE_STRICT = 'strict';

    private const string CAST_MODE_PERMISSIVE = 'permissive';

    /**
     * @param  array<string, mixed>  $customData
     * @param  array<class-string<\JOOservices\Dto\Hydration\PipelineStepInterface>>  $globalPipeline
     */
    public function __construct(
        public ?NamingStrategyInterface $namingStrategy = null,
        public bool $validationEnabled = false,
        public ?SerializationOptions $serializationOptions = null,
        public string $transformerMode = self::TRANSFORMER_MODE_FULL,
        public array $customData = [],
        public string $castMode = self::CAST_MODE_LOOSE,
        public array $globalPipeline = [],
    ) {}

    public function withNamingStrategy(?NamingStrategyInterface $strategy): self
    {
        return new self(
            namingStrategy: $strategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            transformerMode: $this->transformerMode,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
        );
    }

    public function withValidationEnabled(bool $enabled): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $enabled,
            serializationOptions: $this->serializationOptions,
            transformerMode: $this->transformerMode,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
        );
    }

    public function withSerializationOptions(?SerializationOptions $options): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $options,
            transformerMode: $this->transformerMode,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
        );
    }

    public function withTransformerMode(string $mode): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            transformerMode: $mode,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function withCustomData(array $data): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            transformerMode: $this->transformerMode,
            customData: array_merge($this->customData, $data),
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
        );
    }

    /**
     * Create new context with specified cast mode.
     */
    public function withCastMode(string $mode): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            transformerMode: $this->transformerMode,
            customData: $this->customData,
            castMode: $mode,
            globalPipeline: $this->globalPipeline,
        );
    }

    /**
     * Create new context with global pipeline steps.
     *
     * @param  array<class-string<\JOOservices\Dto\Hydration\PipelineStepInterface>>  $steps
     */
    public function withGlobalPipeline(array $steps): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            transformerMode: $this->transformerMode,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $steps,
        );
    }

    /**
     * Check if strict casting mode is enabled.
     */
    public function isStrictMode(): bool
    {
        return $this->castMode === self::CAST_MODE_STRICT;
    }

    /**
     * Check if permissive casting mode is enabled.
     * In permissive mode, failed casts return null instead of throwing exceptions.
     */
    public function isPermissiveMode(): bool
    {
        return $this->castMode === self::CAST_MODE_PERMISSIVE;
    }

    /**
     * Create a new context with permissive casting mode.
     * In permissive mode, all types are treated as nullable and failed casts return null.
     */
    public static function permissive(): self
    {
        return new self(castMode: self::CAST_MODE_PERMISSIVE);
    }

    public function getCustom(string $key, mixed $default = null): mixed
    {
        return $this->customData[$key] ?? $default;
    }

    public function hasCustom(string $key): bool
    {
        return array_key_exists($key, $this->customData);
    }

    public function getSerializationOptions(): SerializationOptions
    {
        return $this->serializationOptions ?? new SerializationOptions;
    }

    public function isFullTransformerMode(): bool
    {
        return $this->transformerMode === self::TRANSFORMER_MODE_FULL;
    }

    /**
     * Create new context with wrapped serialization output.
     *
     * @param  string|null  $key  The key to wrap output with (e.g., 'data', 'users')
     * @return self New instance with wrap configuration
     */
    public function wrap(?string $key): self
    {
        $currentOptions = $this->getSerializationOptions();
        $newOptions = $currentOptions->withWrap($key);

        return $this->withSerializationOptions($newOptions);
    }
}
