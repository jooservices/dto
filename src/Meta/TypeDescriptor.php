<?php

declare(strict_types=1);

namespace JOOservices\Dto\Meta;

use BackedEnum;
use DateTimeInterface;
use JOOservices\Dto\Core\Dto;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionType;
use UnitEnum;

final readonly class TypeDescriptor
{
    public function __construct(
        public string $name,
        public bool $isBuiltin,
        public bool $isNullable,
        public bool $isArray,
        public ?self $arrayItemType,
        public bool $isEnum,
        public ?string $enumClass,
        public bool $isDto,
        public bool $isDateTime,
    ) {}

    public static function fromReflection(?ReflectionType $type): self
    {
        if ($type === null) {
            return self::mixed();
        }

        if (! $type instanceof ReflectionNamedType) {
            return self::mixed();
        }

        $typeName = $type->getName();
        $isBuiltin = $type->isBuiltin();
        $isNullable = $type->allowsNull();

        $isArray = $typeName === 'array';
        $isEnum = false;
        $enumClass = null;
        $isDto = false;
        $isDateTime = false;

        if (! $isBuiltin && class_exists($typeName)) {
            $reflection = new ReflectionClass($typeName);
            $isEnum = $reflection->isEnum();

            if ($isEnum) {
                $enumClass = $typeName;
            }

            $isDto = $reflection->isSubclassOf(Dto::class) || $typeName === Dto::class;
            $isDateTime = $reflection->implementsInterface(DateTimeInterface::class);
        }

        return new self(
            name: $typeName,
            isBuiltin: $isBuiltin,
            isNullable: $isNullable,
            isArray: $isArray,
            arrayItemType: null,
            isEnum: $isEnum,
            enumClass: $enumClass,
            isDto: $isDto,
            isDateTime: $isDateTime,
        );
    }

    public static function mixed(): self
    {
        return new self(
            name: 'mixed',
            isBuiltin: true,
            isNullable: true,
            isArray: false,
            arrayItemType: null,
            isEnum: false,
            enumClass: null,
            isDto: false,
            isDateTime: false,
        );
    }

    public static function forClass(string $className): self
    {
        if (! class_exists($className) && ! interface_exists($className)) {
            return self::mixed();
        }

        $reflection = new ReflectionClass($className);
        $isEnum = $reflection->isEnum();
        $isDto = $reflection->isSubclassOf(Dto::class) || $className === Dto::class;
        $isDateTime = $reflection->implementsInterface(DateTimeInterface::class);

        return new self(
            name: $className,
            isBuiltin: false,
            isNullable: false,
            isArray: false,
            arrayItemType: null,
            isEnum: $isEnum,
            enumClass: $isEnum ? $className : null,
            isDto: $isDto,
            isDateTime: $isDateTime,
        );
    }

    public function withArrayItemType(self $itemType): self
    {
        return new self(
            name: $this->name,
            isBuiltin: $this->isBuiltin,
            isNullable: $this->isNullable,
            isArray: true,
            arrayItemType: $itemType,
            isEnum: $this->isEnum,
            enumClass: $this->enumClass,
            isDto: $this->isDto,
            isDateTime: $this->isDateTime,
        );
    }

    public function withNullable(bool $nullable): self
    {
        return new self(
            name: $this->name,
            isBuiltin: $this->isBuiltin,
            isNullable: $nullable,
            isArray: $this->isArray,
            arrayItemType: $this->arrayItemType,
            isEnum: $this->isEnum,
            enumClass: $this->enumClass,
            isDto: $this->isDto,
            isDateTime: $this->isDateTime,
        );
    }

    public function isScalar(): bool
    {
        return in_array($this->name, ['int', 'float', 'string', 'bool'], true);
    }

    public function isBackedEnum(): bool
    {
        if (! $this->isEnum || $this->enumClass === null) {
            return false;
        }

        return is_subclass_of($this->enumClass, BackedEnum::class);
    }

    public function isUnitEnum(): bool
    {
        if (! $this->isEnum || $this->enumClass === null) {
            return false;
        }

        return is_subclass_of($this->enumClass, UnitEnum::class)
            && ! is_subclass_of($this->enumClass, BackedEnum::class);
    }

    public function acceptsNull(): bool
    {
        return $this->isNullable || $this->name === 'mixed';
    }

    public function isTypedArray(): bool
    {
        return $this->isArray && $this->arrayItemType !== null;
    }
}
