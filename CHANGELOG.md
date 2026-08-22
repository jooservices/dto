# Changelog

All notable changes to this package are documented in this file. Format follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/); versioning follows [Semantic Versioning](https://semver.org/).

> [!WARNING]
> **This changelog starts at `v3.0.0`.** The package was fully rebuilt from scratch — a new codebase with fresh git history.
> `v1.x` and `v2.x` belong to the archived previous implementation and are **not ancestors** of this line.
> **There is no backward compatibility with any previous version:** no shims, no deprecation bridges, no migration path. Upgrading means rewriting your DTO classes against the new API.

## [Unreleased]

## [3.0.0] - 2026-08-22

### Added

- GitHub Actions CI on self-hosted Linux X64 runners: validation, lint/test matrices, dependency security, Gitleaks OSS CLI, Semgrep OSS, Codecov, and Sonar
- CodeQL analysis for GitHub Actions workflows, workflow audit (actionlint + zizmor), commitlint on PR commits, release drafter, Trivy + SBOM on release tags
- Housekeeping workflows: stale bot, first-interaction welcome, PR size labels, weekly Markdown link check

### Changed

- CI uses Docker Compose image caching and Composer `vendor/` caching to reduce job time
- Semgrep runs in OSS mode (no cloud token required)

### BREAKING

- Complete rewrite of every line against a new architecture. Nothing from the previous implementation carries over as-is:
  - Engine: one process-wide Engine (factory-created, injectable) with LRU `ClassMeta` caches and explicit worker `reset()` replaces the per-class static engine store
  - Copy helpers (`with()` / `clone()` / `merge()` / `mergeRecursive()`) build through the constructor from the state view instead of a `toArray()` → `from()` round-trip
  - Exception hierarchy is vendorized under `src/Exceptions/` instead of extending an external package base
- Zero runtime Composer dependencies. Optional integrations are `suggest`-only: `psr/log`, `psr/http-message`
- Laravel adapters and `config()` coupling are fully dropped
- `with()` accepts property names only — never source keys
- Constructor-less DTOs remain unsupported, now enforced consistently at hydration and copy paths
- Removed by design: `ArrayAccess`, PSR-11 discovery (`SupportsDiscovery`), WeakMap engine registry, XML/YAML output, compiled hydrator cache

### Added

Core:

- Immutable `Dto` and mutable `Data` on a shared constructor-promoted base
- `Context`, `CastMode`, `SerializationOptions`, `Optional`, `PartialDtoBuilder`, `ComputesLazyProperties`
- Reflection-based metadata with true-LRU memory cache and content-hash file cache envelope

Factories:

- `from()`, `fromArray()`, `fromJson()`, `fromObject()` for external input
- `tryFrom()` — non-throwing hydration returning `null` on failure
- `fromRequest(ServerRequestInterface)` — PSR-7 parsed body + query string hydration
- `collection()` and `partial()` builders

Instance helpers:

- State-view `with()` accepting arrays and named arguments; patched values cast, everything else untouched
- `merge()`, `mergeRecursive()`, `clone()` / `replicate()` through the same constructor path
- `diff()`, `equals()`, `hash()` operating on the state view; canonical sorted-key JSON hashing
- Standalone instance validation via `validate()`; conditional helpers `when()` / `unless()`

Attributes & mapping:

- `MapTo` — output keys independent of property names / `MapFrom`
- Pipeline steps accept constructor-spread options (same model as `CastWith`)
- Class-level `DiscriminatorMap` resolved after naming / `MapFrom`, failing loudly on missing or unknown values, validated against DTO lineage
- Discriminator key emitted on output when normalizing a parent-typed property holding a subtype

Hydration & normalization:

- PSR-7 request input alongside arrays, JSON strings, and objects
- Output-side naming strategy, opt-in via Context (default keeps property names)
- `toArray()` / `toJson()` parity for nested `JsonSerializable` values
- Serialization filters: `only` / `except` / `maxDepth` / `wrap` / `includeLazy`, applied in one place across all JSON entry points

Quality of life:

- Structured exception payloads with path support and context-scoped payload redaction
- Configurable hydration depth guard

### Changed

- PHP requirement stays `>= 8.5`; extensions `dom` + `libxml` required
- Strict PSR-1 / PSR-4 / PSR-12 (PER-CS 2.0) formatting: Pint `per` preset primary, PHPCS full `PSR12`, PHPStan max level with zero ignores, PHPMD, PHP-CS-Fixer restricted to PHPDoc rules
- Union type casting uses deterministic order: exact type first, then specificity (`int` before `float` in `int|float`), declaration order last
- `#[RequiredIf]` compares values after casting, not raw pre-cast input
- Lazy properties are computed only when serialization actually requests them
- CI: dedicated workflows only (no shared reusable-workflow dependency), self-hosted runner, all PHP jobs in Docker, 85% per-suite coverage floor, Codecov + Sonar uploads required

### Fixed

All items below are defects found in the archived previous implementation; each ships with a named regression test.

Correctness:

- Copy helpers preserve `#[Hidden]` properties and never mix source-key vs property-key spaces; omitted Context no longer resets cast mode / naming / pipelines or silently enables validation
- Permissive cast mode no longer leaks raw `TypeError` for non-nullable scalars
- Polymorphic hydration works in strict mode (discriminator key exempt from unknown-key rejection)
- Lifecycle hooks run on nested DTOs, not just top-level instances
- `Data::update()` / `set()` are mapping-aware
- Partial builders filter on mapped source keys
- Missing env fallbacks continue the `#[DefaultFrom]` resolution chain instead of aborting
- `DateTimeCaster` rejects trailing garbage via `getLastErrors()`
- `INF` / `NaN` rejected in float casting and JSON output; integer overflow is deterministic
- String casting no longer mangles data (`null → ''` quirk removed)
- `wrap` applied consistently to `toArray()`, `toJson()`, and engine JSON output

Security:

- Regex validator hardening: lower input cap, `preg_last_error()` handling, fail-closed on invalid patterns
- Tag-stripping pipeline step removes attributes when allowing tags
- File metadata cache: lock files, verify freshness envelope **before** deserialization, strict class allowlist, no error suppression
- Explicit JSON decode depth and flags with documented limits

[Unreleased]: https://github.com/jooservices/dto/compare/v3.0.0...HEAD
[3.0.0]: https://github.com/jooservices/dto/releases/tag/v3.0.0
