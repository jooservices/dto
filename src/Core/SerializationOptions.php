<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

/**
 * Immutable normalization options for DTO and collection output.
 */
final readonly class SerializationOptions
{
    private const int DEFAULT_MAX_DEPTH = 10;

    /**
     * @param  array<string>|null  $only  Property allow-list
     * @param  array<string>|null  $except  Property deny-list when `$only` is not set
     * @param  int  $maxDepth  Maximum recursion depth during normalization
     * @param  array<string>|null  $includeLazy  null = none, [] = all, list = specific
     * @param  string|null  $wrap  Optional wrapper key for output
     */
    public function __construct(
        public ?array $only = null,
        public ?array $except = null,
        public int $maxDepth = self::DEFAULT_MAX_DEPTH,
        public ?array $includeLazy = null,
        public ?string $wrap = null,
    ) {
    }

    public function shouldInclude(string $propertyName): bool
    {
        if ($this->only !== null) {
            return in_array($propertyName, $this->only, true);
        }

        if ($this->except !== null) {
            return ! in_array($propertyName, $this->except, true);
        }

        return true;
    }

    public function shouldIncludeLazy(string $lazyPropertyName): bool
    {
        if ($this->includeLazy === null) {
            return false;
        }

        if ($this->includeLazy === []) {
            return true;
        }

        return in_array($lazyPropertyName, $this->includeLazy, true);
    }

    public function canDescend(int $currentDepth): bool
    {
        return $currentDepth < $this->maxDepth;
    }

    /**
     * @param  array<string>  $properties
     */
    public function withOnly(array $properties): self
    {
        return new self(
            only: $properties,
            except: null,
            maxDepth: $this->maxDepth,
            includeLazy: $this->includeLazy,
            wrap: $this->wrap,
        );
    }

    /**
     * @param  array<string>  $properties
     */
    public function withExcept(array $properties): self
    {
        return new self(
            only: null,
            except: $properties,
            maxDepth: $this->maxDepth,
            includeLazy: $this->includeLazy,
            wrap: $this->wrap,
        );
    }

    public function withMaxDepth(int $maxDepth): self
    {
        return new self(
            only: $this->only,
            except: $this->except,
            maxDepth: $maxDepth,
            includeLazy: $this->includeLazy,
            wrap: $this->wrap,
        );
    }

    /**
     * @param  array<string>|null  $includeLazy
     */
    public function withIncludeLazy(?array $includeLazy): self
    {
        return new self(
            only: $this->only,
            except: $this->except,
            maxDepth: $this->maxDepth,
            includeLazy: $includeLazy,
            wrap: $this->wrap,
        );
    }

    public function withWrap(?string $wrap): self
    {
        return new self(
            only: $this->only,
            except: $this->except,
            maxDepth: $this->maxDepth,
            includeLazy: $this->includeLazy,
            wrap: $wrap,
        );
    }

    public static function default(): self
    {
        return new self();
    }
}
