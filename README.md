# jooservices/dto

[![CI](https://github.com/jooservices/dto/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/jooservices/dto/actions/workflows/ci.yml)
[![codecov](https://codecov.io/gh/jooservices/dto/graph/badge.svg?token=P53R9GC7UL)](https://codecov.io/gh/jooservices/dto)
[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/jooservices/dto/badge)](https://securityscorecards.dev/viewer/?uri=github.com/jooservices/dto)
[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg)](https://www.php.net/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A PHP 8.5+ attribute-driven DTO and Data library: immutable `Dto` and mutable `Data` objects, constructor-first hydration, opt-in validation, serialization control, collections, and JSON Schema / OpenAPI generation. Zero runtime dependencies.

> [!WARNING]
> **`v3.0.0` is a complete ground-up rebuild of this package and is NOT backward compatible with any previous version (`v1.x`, `v2.x`).**
> Every line was rewritten against a new architecture. There are no legacy shims, no deprecation bridges, and no compatibility code.
> Upgrading means rewriting your DTO classes against the new API — see [About v3.0.0](#about-v300) and the [changelog](CHANGELOG.md).

## About v3.0.0

| | |
| --- | --- |
| Status | **Not released yet** — no tag / Packagist / GitHub Release until the maintainer announces it |
| First public line | `v3.0.0` (the archived `v1.x` / `v2.x` implementation is a separate codebase lineage, not an ancestor of this one) |
| Git history | Fresh repository — clean rewrite, old repo untouched in archive |
| Compatibility | **None with older versions.** Class layout, behavior contracts, exception hierarchy, and engine internals all changed |
| Runtime dependencies | Zero `require` beyond PHP itself — exceptions are vendorized; PSR-3 / PSR-7 integrations are optional via Composer `suggest` |

## Highlights vs the previous line

| Area | Previous (`v2.x`) | This rebuild (`v3.0.0`) |
| --- | --- | --- |
| Dependencies | `jooservices/exceptions ^1.0` runtime require | Zero runtime deps, vendorized exceptions |
| Engine | Per-class static engine store | One process-wide Engine + LRU `ClassMeta` caches + worker `reset()` |
| `with()` / `clone()` / `merge()` | `toArray()` → `from()` round-trip (dropped `#[Hidden]`, mixed key spaces) | State view through the constructor, property-name keys only, patched values cast, named args supported |
| Hashing | Order-dependent `serialize(toArray())` | Canonical sorted-key JSON over the state view |
| Framework coupling | Laravel `config()` inside `#[DefaultFrom]` | Pluggable resolver (env + static method); Laravel adapters fully dropped |
| HTTP input | — | `fromRequest(ServerRequestInterface)` (PSR-7) |
| Coding standards | Pint `laravel` preset, partial PSR-12 | Strict PSR-1 / PSR-4 / PSR-12 (PER-CS 2.0), Pint `per` preset |
| Correctness & security | Known defect register (C1–C19, S1–S6) | All fixed with named regression tests |

## Features

**Core**

- `Dto` (immutable, readonly) and `Data` (mutable counterpart) sharing one base
- `Context`, `CastMode`, `SerializationOptions`, `Optional`, `PartialDtoBuilder`
- Lazy derived serialization via `ComputesLazyProperties`

**Factories**

- `from()`, `fromArray()`, `fromJson()`, `fromObject()` — external input hydration
- `tryFrom()` — non-throwing variant
- `fromRequest(ServerRequestInterface)` — PSR-7 parsed body + query string
- `collection()` for lists, `partial()` for partial-payload builders

**Instance helpers**

- `with()` — immutable copy through the constructor; array or named args (`$dto->with(email: $v)`)
- `merge()`, `mergeRecursive()`, `clone()` / `replicate()`
- `diff()`, `equals()`, `hash()` — state-view comparisons with canonical hashing
- `validate()`, `when()` / `unless()`

**Attributes**

- Mapping: `MapFrom`, `MapTo`, `Hidden`, `DefaultFrom`, class-level `DiscriminatorMap`
- Casting / transforms: `CastWith`, `TransformWith`, `StrictType`, `Pipeline` (constructor-spread step options)
- Validation: `Required`, `RequiredIf`, `Email`, `Url`, `Regex`, `Length`, `Min`, `Max`, `Between`, `Valid`

**Hydration & casting**

- Arrays, JSON strings, simple objects, PSR-7 requests
- Input naming strategies (camelCase / snake_case); output-side naming opt-in via Context
- Global + property pipelines with options; input normalizers
- Scalar, enum, `DateTimeInterface`, nested DTO, PHPDoc typed arrays (`Type[]`, `array<Type>`, `list<Type>`), native union types in stable documented order

**Validation, normalization, collections**

- Opt-in validation via Context plus standalone instance validation; rule registry extensible via attributes
- `toArray()` / `toJson()` / `jsonSerialize()`, transformers, lazy properties
- Serialization filters: `only` / `except` / `maxDepth` / `wrap` / `includeLazy`
- `DataCollection` and `PaginatedCollection` (duck-typed paginator support)

**Schema, meta, exceptions**

- `JsonSchemaGenerator` and `OpenApiGenerator` emitting self-contained recursive `$ref` graphs
- Reflection-based metadata: true-LRU memory cache + file cache with content-hash freshness envelope
- Structured exception hierarchy (hydration / mapping / cast / validation) with path support and payload redaction

## Requirements

- PHP `>= 8.5`
- Extensions: `dom`, `libxml`
- Optional: `psr/log` (Engine debug logging), `psr/http-message` (`fromRequest()`)
- Docker (recommended — all local tooling runs in `php:8.5-cli-bookworm`)

## Installation

> [!NOTE]
> **Not published yet.** The command below becomes valid once the maintainer tags `v3.0.0` and pushes to Packagist.

```bash
composer require jooservices/dto:^3.0
```

## Quick start

```php
use JOOservices\Dto\Attributes\MapFrom;
use JOOservices\Dto\Core\Dto;

final class UserDto extends Dto
{
    public function __construct(
        public readonly string $id,
        #[MapFrom('email_address')]
        public readonly string $email,
        public readonly \DateTimeImmutable $createdAt,
    ) {}
}

// External input — source keys resolved through MapFrom / naming strategy
$user = UserDto::from([
    'id' => 'u_123',
    'email_address' => 'john@example.com',
    'createdAt' => '2026-01-15T10:30:00+00:00',
]);

// Immutable copy — property-name keys only, patched value cast to string
$updated = $user->with(email: 'other@example.com');

$updated->toArray();  // ['id' => 'u_123', 'email' => 'other@example.com', ...]
$updated->toJson();
```

## Design contract

- Every DTO declares a constructor with **public promoted properties**; constructor-less classes are unsupported.
- Each entry point owns exactly one key space:

| Entry point | Key space |
| --- | --- |
| `new UserDto(...)` | Property names — already-typed values |
| `UserDto::from()` / `fromArray()` / `fromJson()` / `fromObject()` / `fromRequest()` | Source keys — resolved via `MapFrom` / naming strategy |
| `$dto->with(...)` / `merge(...)` | Property names only — immutable copies built through the constructor |

- Validation is opt-in via `Context`; casting and pipelines never silently change scope when a Context argument is omitted.

## Documentation

- [Changelog](CHANGELOG.md) — starts at `v3.0.0`; earlier releases belong to the retired implementation
- [`AGENTS.md`](AGENTS.md) — contributor/agent working agreement

## Development

All PHP tooling runs inside Docker (`php:8.5-cli-bookworm` via Docker Compose).

```bash
make build     # build the tooling image
make install   # composer install in the container
make shell     # interactive container shell
```

| Command | Purpose |
| --- | --- |
| `make validate` | `composer validate --strict` |
| `make lint` | Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer |
| `make test` | PHPUnit (Unit + Integration) |
| `make test-coverage` | PHPUnit with Clover coverage |
| `make audit` | Composer audit |
| `make bench` | phpbench |
| `make ci` | lint + coverage run (local CI parity) |

Every linter runs at **maximum strictness with no ignore lists** — fix issues at the source instead of suppressing them.

IDE setup: Cursor / VS Code — install recommended workspace extensions; format-on-save runs Pint via `tools/pint` (Docker). PHPStorm — inspection profile + Pint file watcher.

## Branch model & CI

- `master` — production; `develop` — integration
- Feature/fix branches from `develop`, PR back into `develop`; releases via `release/<version>` → `master`; hotfixes from `master`; tags from `master`
- PRs required, all CI checks green before merge

Required CI chain (dedicated workflows on self-hosted runner `runner1`, PHP jobs in Docker):

```text
validate → lint → test (+85% per-suite coverage floor) → security → coverage upload (Codecov + Sonar)
```

Workflows (all on self-hosted `runner1`):

| Workflow | Purpose |
| --- | --- |
| `ci.yml` | validate → lint → test (+85% floor) → security → Codecov + Sonar |
| `commitlint.yml` | Conventional Commits on every PR commit |
| `codeql.yml` | CodeQL analysis for GitHub Actions workflows |
| `workflow-audit.yml` | actionlint + zizmor on workflow files |
| `release.yml` | tag gates, Trivy, SBOM, GitHub Release, Packagist |
| `release-drafter.yml` | draft release notes from merged PRs |
| `semantic-pr.yml` | Conventional Commits PR title |
| `pr-labeler.yml` / `pr-size-labeler.yml` | path and size labels |
| `scorecard.yml` | OpenSSF Scorecard |
| `link-check.yml` | weekly Markdown link check |
| `stale.yml` / `first-interaction.yml` | housekeeping and contributor welcome |

Also: Dependabot (Composer + GitHub Actions), CODEOWNERS, labeler config.

**CI secrets (organization level):** `CODECOV_TOKEN`, `SONAR_TOKEN`, and `SONAR_HOST_URL` live under [jooservices organization secrets](https://github.com/organizations/jooservices/settings/secrets/actions) — not per-repo. Grant this repository access when onboarding. No release tag is required to test CI; any push or PR to `develop` or `master` runs the pipeline.

Quality gates: Pint (`per` preset) · PHPCS full `PSR12` · PHPStan max level, zero ignores · PHPMD · PHP-CS-Fixer (PHPDoc-only).

## Community

- [Contributing guide](CONTRIBUTING.md) — setup, git workflow, commit convention, quality gates, PR rules
- [Security policy](SECURITY.md) — how to report vulnerabilities privately
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Support](SUPPORT.md)
- [Governance](GOVERNANCE.md)

## License

MIT — see [LICENSE](LICENSE).
