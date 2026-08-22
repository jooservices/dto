<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

use InvalidArgumentException;
use JOOservices\Dto\Hydration\Naming\NamingStrategyInterface;
use JOOservices\Dto\Hydration\PipelineStepInterface;

/**
 * Immutable runtime options for hydration and normalization.
 */
final readonly class Context
{
    public string $castMode;

    /**
     * @param  array<string, mixed>  $customData
     * @param  array<class-string<PipelineStepInterface>>  $globalPipeline
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        public bool $validationEnabled,
        public bool $sourceKeyOut,
        public ?NamingStrategyInterface $namingStrategy = null,
        public ?SerializationOptions $serializationOptions = null,
        public array $customData = [],
        string | CastMode $castMode = CastMode::Loose,
        public array $globalPipeline = [],
        public ?bool $rejectUnknownKeys = null,
        public ?bool $blockCoercion = null,
    ) {
        $this->castMode = self::normalizeCastMode($castMode);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function loose(): self
    {
        return new self(validationEnabled: false, sourceKeyOut: false);
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function permissive(): self
    {
        return new self(
            validationEnabled: false,
            sourceKeyOut: false,
            castMode: CastMode::Permissive,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function strict(): self
    {
        return new self(
            validationEnabled: false,
            sourceKeyOut: false,
            castMode: CastMode::Strict,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withNamingStrategy(?NamingStrategyInterface $strategy): self
    {
        return new self(
            namingStrategy: $strategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withValidationEnabled(bool $enabled): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $enabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withSerializationOptions(?SerializationOptions $options): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $options,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @param  array<string, mixed>  $data
     *
     * @throws InvalidArgumentException
     */
    public function withCustomData(array $data): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: array_merge($this->customData, $data),
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withCastMode(string | CastMode $castMode): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @param  array<class-string<PipelineStepInterface>>  $pipeline
     *
     * @throws InvalidArgumentException
     */
    public function withGlobalPipeline(array $pipeline): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $pipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withRejectUnknownKeys(?bool $rejectUnknownKeys): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withDisallowScalarCoercion(?bool $blockCoercion): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $blockCoercion,
            sourceKeyOut: $this->sourceKeyOut,
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    public function withSourceNamingOnOutput(bool $enabled): self
    {
        return new self(
            namingStrategy: $this->namingStrategy,
            validationEnabled: $this->validationEnabled,
            serializationOptions: $this->serializationOptions,
            customData: $this->customData,
            castMode: $this->castMode,
            globalPipeline: $this->globalPipeline,
            rejectUnknownKeys: $this->rejectUnknownKeys,
            blockCoercion: $this->blockCoercion,
            sourceKeyOut: $enabled,
        );
    }

    public function shouldRejectUnknownKeys(): bool
    {
        if ($this->rejectUnknownKeys !== null) {
            return $this->rejectUnknownKeys;
        }

        return $this->castMode === CastMode::Strict->value;
    }

    public function shouldDisallowScalarCoercion(): bool
    {
        if ($this->blockCoercion !== null) {
            return $this->blockCoercion;
        }

        return $this->castMode === CastMode::Strict->value;
    }

    public function serializationOptionsOrDefault(): SerializationOptions
    {
        return $this->serializationOptions ?? new SerializationOptions();
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function defaultLoose(): self
    {
        return (new SharedDefaults())->looseContext();
    }

    /**
     * @throws InvalidArgumentException
     */
    public static function defaultValidation(): self
    {
        return (new SharedDefaults())->validationContext();
    }

    /**
     * @throws InvalidArgumentException
     */
    private static function normalizeCastMode(string | CastMode $castMode): string
    {
        if ($castMode instanceof CastMode) {
            return $castMode->value;
        }

        $allowed = ['loose', 'strict', 'permissive'];
        if (! in_array($castMode, $allowed, true)) {
            throw new InvalidArgumentException(
                'Unsupported cast mode "' . $castMode . '". Expected one of: ' . implode(', ', $allowed),
            );
        }

        return $castMode;
    }
}
