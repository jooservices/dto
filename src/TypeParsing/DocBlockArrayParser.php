<?php

declare(strict_types=1);

namespace JOOservices\Dto\TypeParsing;

use JOOservices\Dto\Meta\TypeDescriptor;
use ReflectionClass;

final class DocBlockArrayParser
{
    /**
     * @param  class-string|null  $declaringClass
     */
    public function arrayItemType(?string $docComment, ?string $declaringClass = null): ?TypeDescriptor
    {
        if ($docComment === null) {
            return null;
        }

        if (preg_match('/@var\s+(?:list|array)<\s*([^>\s]+)\s*>/', $docComment, $match) === 1) {
            return $this->named($match[1], $declaringClass);
        }

        if (preg_match('/@var\s+([^\s\[]+)\[\]/', $docComment, $match) === 1) {
            return $this->named($match[1], $declaringClass);
        }

        return null;
    }

    /**
     * @param  class-string|null  $declaringClass
     */
    private function named(string $name, ?string $declaringClass): TypeDescriptor
    {
        $name = $this->resolveClassName(ltrim($name, '\\'), $declaringClass);
        if (in_array($name, ['int', 'float', 'string', 'bool', 'mixed'], true)) {
            if ($name === 'mixed') {
                return new TypeDescriptor(
                    kind: TypeDescriptor::KIND_MIXED,
                    nullability: TypeDescriptor::NULLABLE,
                );
            }

            return new TypeDescriptor(
                kind: TypeDescriptor::KIND_BUILTIN,
                builtin: $name,
                nullability: TypeDescriptor::REQUIRED,
            );
        }

        /** @var class-string $name */
        return new TypeDescriptor(
            kind: TypeDescriptor::KIND_CLASS,
            className: $name,
            nullability: TypeDescriptor::REQUIRED,
        );
    }

    /**
     * @param  class-string|null  $declaringClass
     */
    private function resolveClassName(string $name, ?string $declaringClass): string
    {
        if (str_contains($name, '\\') || $declaringClass === null) {
            return $name;
        }

        /** @var class-string $declaringClass */
        $namespace = (new ReflectionClass($declaringClass))->getNamespaceName();
        if ($namespace === '') {
            return $name;
        }

        $candidate = $namespace . '\\' . $name;
        if (class_exists($candidate) || interface_exists($candidate)) {
            return $candidate;
        }

        return $name;
    }
}
