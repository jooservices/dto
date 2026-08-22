<?php

declare(strict_types=1);

namespace JOOservices\Dto\Casting\Casters;

use JOOservices\Dto\Casting\CasterInterface;
use JOOservices\Dto\Core\Context;
use JOOservices\Dto\Exceptions\CastException;
use JOOservices\Dto\Meta\PropertyMeta;
use JOOservices\Dto\Meta\TypeDescriptor;

final class StringCaster implements CasterInterface
{
    public function __construct(
        private readonly ScalarCastSupport $support = new ScalarCastSupport(),
    ) {
    }

    public function supports(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): bool
    {
        unset($property, $ctx, $value);

        return $type->kind === TypeDescriptor::KIND_BUILTIN
            && $type->builtin === 'string';
    }

    /**
     * @throws CastException
     */
    public function cast(TypeDescriptor $type, PropertyMeta $property, mixed $value, ?Context $ctx): mixed
    {
        unset($type);

        if ($value === null) {
            return $this->castNull($property);
        }

        return $this->toString($property, $value, $ctx);
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
            expectedType: 'string',
            givenType: 'null',
        );
    }

    /**
     * @throws CastException
     */
    private function toString(PropertyMeta $property, mixed $value, ?Context $ctx): string
    {
        if (is_string($value)) {
            return $value;
        }

        $this->support->rejectScalarCoercion($property, 'string', $value, $ctx);

        if (is_int($value) || is_float($value)) {
            $this->support->rejectNonFinite($property, (float) $value, 'string');

            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        throw $this->support->mismatch($property, 'string', $value);
    }
}
