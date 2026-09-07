# jooservices/dto

[![CI](https://github.com/jooservices/dto/actions/workflows/ci.yml/badge.svg?branch=develop)](https://github.com/jooservices/dto/actions/workflows/ci.yml)
[![Coverage (develop)](https://codecov.io/gh/jooservices/dto/branch/develop/graph/badge.svg?token=P53R9GC7UL)](https://codecov.io/gh/jooservices/dto/branch/develop)
[![Quality Gate (master)](https://sonarcloud.io/api/project_badges/measure?project=jooservices_dto&metric=alert_status)](https://sonarcloud.io/summary/new_code?id=jooservices_dto)
[![OpenSSF Scorecard](https://api.securityscorecards.dev/projects/github.com/jooservices/dto/badge)](https://securityscorecards.dev/viewer/?uri=github.com/jooservices/dto)
[![PHP Version](https://img.shields.io/badge/PHP-8.5%2B-blue.svg)](https://www.php.net/)
[![GitHub Release](https://img.shields.io/github/v/release/jooservices/dto?display_name=tag)](https://github.com/jooservices/dto/releases)
[![Packagist Version](https://img.shields.io/packagist/v/jooservices/dto)](https://packagist.org/packages/jooservices/dto)
[![Total Downloads](https://img.shields.io/packagist/dt/jooservices/dto)](https://packagist.org/packages/jooservices/dto)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

A PHP 8.5+ attribute-driven DTO and Data library: immutable `Dto` and mutable `Data` objects, constructor-first hydration, opt-in validation, serialization control, collections, and JSON Schema / OpenAPI generation. One runtime dependency: `psr/http-message` (interface-only).

> [!WARNING]
> **`v3.0.0` is a complete ground-up rebuild of this package and is NOT backward compatible with any previous version (`v1.x`, `v2.x`).**
> Rewrite DTO classes for the v3 API before upgrading; there are no legacy shims or deprecation bridges. See the [changelog](CHANGELOG.md).

## Upgrade highlights

- v3 has a new DTO/Data engine, constructor-first hydration, and explicit key-space rules.
- `jooservices/exceptions` is no longer a runtime dependency; `psr/http-message` supports `fromRequest()`.
- `with()`, `merge()`, and `clone()` use property names and rebuild through the constructor.

## Features

- Immutable `Dto` and mutable `Data` objects with typed, constructor-first hydration.
- Factories for arrays, JSON, objects, and PSR-7 requests; collection and partial-payload support.
- Attribute-driven mapping, casting, transforms, validation, and serialization control.
- Immutable copies, merging, comparison, hashing, and lazy derived properties.
- JSON Schema and OpenAPI generation, structured exceptions, and cached reflection metadata.

## Requirements

- PHP `>= 8.5`
- Extensions: `dom`, `libxml`
- `psr/http-message` (`fromRequest()`), optional: `psr/log` (Engine debug logging)
- Docker (recommended — all local tooling runs in `php:8.5-cli-bookworm`)

## Installation

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

## Design notes

- Every DTO declares a constructor with **public promoted properties**; constructor-less classes are unsupported.
- Each entry point owns exactly one key space:

| Entry point | Key space |
| --- | --- |
| `new UserDto(...)` | Property names — already-typed values |
| `UserDto::from()` / `fromArray()` / `fromJson()` / `fromObject()` / `fromRequest()` | Source keys — resolved via `MapFrom` / naming strategy |
| `$dto->with(...)` / `merge(...)` | Property names only — immutable copies built through the constructor |

- Validation is opt-in via `Context`; casting and pipelines never silently change scope when a Context argument is omitted.

## Documentation

- [Changelog](CHANGELOG.md) — version history and upgrade notes
- [Contributing guide](CONTRIBUTING.md) — setup, quality gates, and pull requests
- [Development workflows](WORKFLOWS.md) — branches, CI, releases, and repository automation
- [Security policy](SECURITY.md) — private vulnerability reporting

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

## Community

- [Contributing guide](CONTRIBUTING.md) — setup, git workflow, commit convention, quality gates, PR rules
- [Security policy](SECURITY.md) — how to report vulnerabilities privately
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Support](SUPPORT.md)
- [Governance](GOVERNANCE.md)

## License

MIT — see [LICENSE](LICENSE).
