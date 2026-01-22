<?php

declare(strict_types=1);

namespace JOOservices\Dto\Core;

final readonly class SerializationOptions
{
    private const int DEFAULT_MAX_DEPTH = 10;

    /**
     * @param  array<string>|null  $only  Whitelist of property names to include
     * @param  array<string>|null  $except  Blacklist of property names to exclude
     * @param  array<string>|null  $includeLazy  Lazy properties to include (null = none, [] = all, ['name'] = specific)
     * @param  string|null  $wrap  Optional key to wrap the output (e.g., 'data' produces ['data' => [...]])
     */
    public function __construct(
        public ?array $only = null,
        public ?array $except = null,
        public int $maxDepth = self::DEFAULT_MAX_DEPTH,
        public ?array $includeLazy = null,
        public ?string $wrap = null,
    ) {}

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

    /**
     * Check if a lazy property should be included in serialization.
     *
     * @param  string  $lazyPropertyName  Name of the lazy property
     * @return bool True if should be included
     */
    public function shouldIncludeLazy(string $lazyPropertyName): bool
    {
        // null = don't include any lazy properties (default)
        if ($this->includeLazy === null) {
            return false;
        }

        // [] = include all lazy properties
        if ($this->includeLazy === []) {
            return true;
        }

        // ['avatar', 'stats'] = include only specified ones
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

    public function withMaxDepth(int $depth): self
    {
        return new self(
            only: $this->only,
            except: $this->except,
            maxDepth: $depth,
            includeLazy: $this->includeLazy,
            wrap: $this->wrap,
        );
    }

    /**
     * Create new options with specified lazy properties to include.
     *
     * @param  array<string>|null  $lazyProperties  Array of lazy property names, [] for all, null for none
     * @return self New instance with lazy properties configuration
     */
    public function withIncludeLazy(?array $lazyProperties): self
    {
        return new self(
            only: $this->only,
            except: $this->except,
            maxDepth: $this->maxDepth,
            includeLazy: $lazyProperties,
            wrap: $this->wrap,
        );
    }

    /**
     * Create new options with specified wrap key.
     *
     * @param  string|null  $key  The key to wrap the output with, null to remove wrapping
     * @return self New instance with wrap configuration
     */
    public function withWrap(?string $key): self
    {
        return new self(
            only: $this->only,
            except: $this->except,
            maxDepth: $this->maxDepth,
            includeLazy: $this->includeLazy,
            wrap: $key,
        );
    }
}
