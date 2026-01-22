<?php

declare(strict_types=1);

namespace JOOservices\Dto\Normalization\Transformers;

use DateTimeInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Normalization\TransformerInterface;

final class DateTimeTransformer implements TransformerInterface
{
    private const string DEFAULT_FORMAT = 'Y-m-d\TH:i:sP';

    public function __construct(
        private readonly string $format = self::DEFAULT_FORMAT,
    ) {}

    public function supports(PropertyMeta $property, mixed $value): bool
    {
        return $value instanceof DateTimeInterface;
    }

    public function transform(PropertyMeta $property, mixed $value, ?Context $ctx): string
    {
        if (! $value instanceof DateTimeInterface) {
            return '';
        }

        return $value->format($this->format);
    }
}
