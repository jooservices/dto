<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

/**
 * Describes a property or parameter type for casting/hydration.
 */
final readonly class TypeDescriptor
{
    public const string KIND_BUILTIN = 'builtin';

    public const string KIND_CLASS = 'class';

    public const string KIND_ENUM = 'enum';

    public const string KIND_ARRAY = 'array';

    public const string KIND_UNION = 'union';

    public const string KIND_INTERSECTION = 'intersection';

    public const string KIND_MIXED = 'mixed';

    public const string NULLABLE = 'nullable';

    public const string REQUIRED = 'required';

    /**
     * @param  list<self>  $members  Union/intersection members or array item type as single member
     * @param  class-string|null  $className
     */
    public function __construct(
        public string $kind,
        public ?string $builtin = null,
        public ?string $className = null,
        public array $members = [],
        public string $nullability = self::REQUIRED,
    ) {
    }

    public function allowsNull(): bool
    {
        return $this->nullability === self::NULLABLE;
    }

    public static function mixed(): self
    {
        return new self(kind: self::KIND_MIXED, nullability: self::NULLABLE);
    }

    public static function builtin(string $name): self
    {
        return new self(kind: self::KIND_BUILTIN, builtin: $name, nullability: self::REQUIRED);
    }

    /**
     * @param  class-string  $className
     */
    public static function classType(string $className): self
    {
        return new self(kind: self::KIND_CLASS, className: $className, nullability: self::REQUIRED);
    }
}
