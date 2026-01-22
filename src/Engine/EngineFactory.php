<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Casting\CasterRegistry;
use JOOservices\Dto\Casting\CasterRegistryInterface;
use JOOservices\Dto\Casting\Casters\DateTimeCaster;
use JOOservices\Dto\Casting\Casters\EnumCaster;
use JOOservices\Dto\Casting\Casters\ScalarCaster;
use JOOservices\Dto\Hydration\Hydrator;
use JOOservices\Dto\Hydration\InputNormalizers\ArrayInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\JsonInputNormalizer;
use JOOservices\Dto\Hydration\InputNormalizers\ObjectInputNormalizer;
use JOOservices\Dto\Hydration\Mapper;
use JOOservices\Dto\Meta\MemoryMetaCache;
use JOOservices\Dto\Meta\MetaCacheInterface;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Normalizer;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Normalization\TransformerRegistryInterface;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;
use JOOservices\Dto\Validation\ValidatorRegistry;
use JOOservices\Dto\Validation\ValidatorRegistryInterface;
use JOOservices\Dto\Validation\Validators\BetweenValidator;
use JOOservices\Dto\Validation\Validators\EmailValidator;
use JOOservices\Dto\Validation\Validators\LengthValidator;
use JOOservices\Dto\Validation\Validators\MaxValidator;
use JOOservices\Dto\Validation\Validators\MinValidator;
use JOOservices\Dto\Validation\Validators\RegexValidator;
use JOOservices\Dto\Validation\Validators\RequiredIfValidator;
use JOOservices\Dto\Validation\Validators\RequiredValidator;
use JOOservices\Dto\Validation\Validators\UrlValidator;
use JOOservices\Dto\Validation\Validators\ValidValidator;

final class EngineFactory
{
    private ?MetaCacheInterface $metaCache = null;

    private ?CasterRegistryInterface $casterRegistry = null;

    private ?TransformerRegistryInterface $transformerRegistry = null;

    private ?ValidatorRegistryInterface $validatorRegistry = null;

    public function withMetaCache(MetaCacheInterface $cache): self
    {
        $clone = clone $this;
        $clone->metaCache = $cache;

        return $clone;
    }

    public function withCasterRegistry(CasterRegistryInterface $registry): self
    {
        $clone = clone $this;
        $clone->casterRegistry = $registry;

        return $clone;
    }

    public function withTransformerRegistry(TransformerRegistryInterface $registry): self
    {
        $clone = clone $this;
        $clone->transformerRegistry = $registry;

        return $clone;
    }

    public function withValidatorRegistry(ValidatorRegistryInterface $registry): self
    {
        $clone = clone $this;
        $clone->validatorRegistry = $registry;

        return $clone;
    }

    public function create(): Engine
    {
        $metaCache = $this->metaCache ?? new MemoryMetaCache;
        $metaFactory = new MetaFactory($metaCache);

        $casterRegistry = $this->casterRegistry ?? $this->createDefaultCasterRegistry();
        $transformerRegistry = $this->transformerRegistry ?? $this->createDefaultTransformerRegistry();
        $validatorRegistry = $this->validatorRegistry ?? $this->createDefaultValidatorRegistry();

        $mapper = new Mapper;
        $hydrator = new Hydrator($mapper, $casterRegistry, $metaFactory, $validatorRegistry);
        $normalizer = new Normalizer($transformerRegistry, $metaFactory);

        $engine = new Engine($metaFactory, $hydrator, $normalizer);

        return $engine
            ->addInputNormalizer(new ArrayInputNormalizer)
            ->addInputNormalizer(new JsonInputNormalizer)
            ->addInputNormalizer(new ObjectInputNormalizer);
    }

    private function createDefaultCasterRegistry(): CasterRegistry
    {
        $registry = new CasterRegistry;

        $registry->register(new ScalarCaster, 10);
        $registry->register(new EnumCaster, 20);
        $registry->register(new DateTimeCaster, 30);

        return $registry;
    }

    private function createDefaultTransformerRegistry(): TransformerRegistry
    {
        $registry = new TransformerRegistry;

        $registry->register(new DateTimeTransformer, 10);
        $registry->register(new EnumTransformer, 20);

        return $registry;
    }

    private function createDefaultValidatorRegistry(): ValidatorRegistry
    {
        $registry = new ValidatorRegistry;

        // Required validators have higher priority to run first
        $registry->register(new RequiredValidator, 100);
        $registry->register(new RequiredIfValidator, 90);

        // Type/format validators
        $registry->register(new EmailValidator, 50);
        $registry->register(new UrlValidator, 50);
        $registry->register(new RegexValidator, 50);

        // Numeric validators
        $registry->register(new MinValidator, 40);
        $registry->register(new MaxValidator, 40);
        $registry->register(new BetweenValidator, 40);

        // String validators
        $registry->register(new LengthValidator, 30);

        // Nested validation (needs registry for recursive validation)
        $registry->register(new ValidValidator, 10);

        return $registry;
    }
}
