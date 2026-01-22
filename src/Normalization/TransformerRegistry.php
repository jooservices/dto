<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization;

use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;

final class TransformerRegistry implements TransformerRegistryInterface
{
    /** @var array<array{transformer: TransformerInterface, priority: int}> */
    private array $transformers = [];

    private bool $sorted = true;

    public function register(TransformerInterface $transformer, int $priority = 0): void
    {
        $this->transformers[] = [
            'transformer' => $transformer,
            'priority' => $priority,
        ];

        $this->sorted = false;
    }

    public function get(PropertyMeta $property, mixed $value): ?TransformerInterface
    {
        $this->ensureSorted();

        foreach ($this->transformers as $item) {
            if ($item['transformer']->supports($property, $value)) {
                return $item['transformer'];
            }
        }

        return null;
    }

    public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        $transformer = $this->get($property, $value);

        if ($transformer === null) {
            return $value;
        }

        return $transformer->transform($property, $value, $ctx);
    }

    public function canTransform(PropertyMeta $property, mixed $value): bool
    {
        return $this->get($property, $value) !== null;
    }

    private function ensureSorted(): void
    {
        if ($this->sorted) {
            return;
        }

        usort(
            $this->transformers,
            static fn (array $first, array $second): int => $second['priority'] <=> $first['priority'],
        );

        $this->sorted = true;
    }
}
