<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

final class TransformerRegistry implements TransformerRegistryInterface
{
    /** @var list<TransformerInterface> */
    private array $transformers = [];

    public function register(TransformerInterface $transformer): void
    {
        $this->transformers[] = $transformer;
    }

    public function transform(mixed $value): mixed
    {
        foreach ($this->transformers as $transformer) {
            if ($transformer->supports($value)) {
                return $transformer->transform($value);
            }
        }

        return $value;
    }
}
