<?php

declare(strict_types=1);

namespace JOOservices\Dto\Engine;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\MetaFactory;
use JOOservices\Dto\Normalization\Normalizer;
use JOOservices\Dto\Normalization\NormalizerPropertySupport;
use JOOservices\Dto\Normalization\NormalizerValueTransformer;
use JOOservices\Dto\Normalization\TransformerRegistry;
use JOOservices\Dto\Normalization\Transformers\DateTimeTransformer;
use JOOservices\Dto\Normalization\Transformers\EnumTransformer;

final class NormalizerFactory
{
    public function create(MetaFactory $metaFactory, Context $defaultContext): Normalizer
    {
        $transformers = new TransformerRegistry();
        $transformers->register(new DateTimeTransformer());
        $transformers->register(new EnumTransformer());

        return new Normalizer(
            $metaFactory,
            new NormalizerPropertySupport(),
            new NormalizerValueTransformer($transformers),
            $defaultContext,
        );
    }
}
