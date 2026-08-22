<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

final class FloatCaster implements CasterInterface
{
    public function __construct(
        private readonly ScalarCastSupport $support = new ScalarCastSupport(),
    ) {
    }

    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($property, $ctx, $value);

        return $type->kind === TypeDescriptor::KIND_BUILTIN
            && $type->builtin === 'float';
    }

    /**
     * @throws CastException
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        unset($type);
        $mode = $ctx === null ? CastMode::Loose->value : $ctx->castMode;

        if ($value === null) {
            return $this->castNull($property);
        }

        return $this->toFloat($property, $value, $mode, $ctx);
    }

    /**
     * @throws CastException
     */
    private function castNull(PropertyMeta $property): mixed
    {
        if ($property->allowsNull || $property->type->allowsNull()) {
            return null;
        }

        throw new CastException(
            message: 'Null is not allowed.',
            path: $property->name,
            expectedType: 'float',
            givenType: 'null',
        );
    }

    /**
     * @throws CastException
     */
    private function toFloat(PropertyMeta $property, mixed $value, string $mode, ?Context $ctx): float
    {
        if (is_float($value)) {
            $this->support->rejectNonFinite($property, $value, 'float');

            return $value;
        }

        $this->support->rejectScalarCoercion($property, 'float', $value, $ctx);

        if (is_int($value)) {
            return (float) $value;
        }

        if (is_bool($value)) {
            return $value ? 1.0 : 0.0;
        }

        if (is_string($value) && is_numeric($value)) {
            $float = (float) $value;
            $this->support->rejectNonFinite($property, $float, 'float');

            return $float;
        }

        if ($mode === CastMode::Permissive->value && is_numeric($value)) {
            $float = (float) $value;
            $this->support->rejectNonFinite($property, $float, 'float');

            return $float;
        }

        throw $this->support->mismatch($property, 'float', $value);
    }
}
