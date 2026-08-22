<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

final class BoolCaster implements CasterInterface
{
    public function __construct(
        private readonly ScalarCastSupport $support = new ScalarCastSupport(),
    ) {
    }

    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($property, $ctx, $value);

        return $type->kind === TypeDescriptor::KIND_BUILTIN
            && $type->builtin === 'bool';
    }

    /**
     * @throws CastException
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        unset($type);
        $mode = $ctx === null ? CastMode::Loose->value : $ctx->castMode;

        if ($value === null) {
            return $this->castNull($property, $mode);
        }

        return $this->toBool($property, $value, $mode, $ctx);
    }

    /**
     * @throws CastException
     */
    private function castNull(PropertyMeta $property, string $mode): mixed
    {
        if ($property->allowsNull || $property->type->allowsNull()) {
            return null;
        }

        if ($mode === CastMode::Permissive->value) {
            return false;
        }

        throw new CastException(
            message: 'Null is not allowed.',
            path: $property->name,
            expectedType: 'bool',
            givenType: 'null',
        );
    }

    /**
     * @throws CastException
     */
    private function toBool(PropertyMeta $property, mixed $value, string $mode, ?Context $ctx): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $this->support->rejectScalarCoercion($property, 'bool', $value, $ctx);

        if (is_int($value) || is_float($value)) {
            return (float) $value !== 0.0;
        }

        if (is_string($value)) {
            return $this->boolFromString($property, $value, $mode);
        }

        if ($mode === CastMode::Permissive->value) {
            return (bool) $value;
        }

        throw $this->support->mismatch($property, 'bool', $value);
    }

    /**
     * @throws CastException
     */
    private function boolFromString(PropertyMeta $property, string $value, string $mode): bool
    {
        $normalized = strtolower(trim($value));
        if (in_array($normalized, ['true', '1', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['false', '0', 'no', 'off', ''], true)) {
            return false;
        }

        if ($mode === CastMode::Permissive->value) {
            return (bool) $value;
        }

        throw $this->support->mismatch($property, 'bool', $value);
    }
}
