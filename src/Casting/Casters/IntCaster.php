<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\CastMode;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

final class IntCaster implements CasterInterface
{
    public function __construct(
        private readonly ScalarCastSupport $support = new ScalarCastSupport(),
        private readonly IntStringCaster $stringCaster = new IntStringCaster(),
    ) {
    }

    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($property, $ctx, $value);

        return $type->kind === TypeDescriptor::KIND_BUILTIN
            && $type->builtin === 'int';
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

        return $this->toInt($property, $value, $mode, $ctx);
    }

    /**
     * @throws CastException
     */
    private function castNull(PropertyMeta $property, string $mode): mixed
    {
        if ($property->allowsNull || $property->type->allowsNull()) {
            return null;
        }

        unset($mode);

        throw new CastException(
            message: 'Null is not allowed.',
            path: $property->name,
            expectedType: 'int',
            givenType: 'null',
        );
    }

    /**
     * @throws CastException
     */
    private function toInt(PropertyMeta $property, mixed $value, string $mode, ?Context $ctx): int
    {
        if (is_int($value)) {
            return $value;
        }

        $this->support->rejectScalarCoercion($property, 'int', $value, $ctx);

        if (is_float($value)) {
            $this->support->rejectNonFinite($property, $value, 'int');

            return (int) $value;
        }

        if (is_bool($value)) {
            return $value ? 1 : 0;
        }

        if (is_string($value)) {
            return $this->stringCaster->cast($property, $value, $mode);
        }

        if ($mode === CastMode::Permissive->value) {
            return $this->permissiveInt($value);
        }

        throw $this->support->mismatch($property, 'int', $value);
    }

    private function permissiveInt(mixed $value): int
    {
        /** @phpstan-ignore cast.int */
        return (int) $value;
    }
}
