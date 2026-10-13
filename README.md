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
- Docker with Docker Compose v2 (required for local development and quality gates)

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

PHP tooling runs inside the DTO Project Docker image (`php:8.5-cli-trixie`).
Local security, workflow, and Dockerfile scanners run in separate one-shot
Project Docker services. SonarQube Community Build runs on demand in JOOservices
Shared Docker; the scanner remains in Project Docker. No host PHP, Composer,
Gitleaks, or scanner installation is required.

```bash
make install          # build PHP + Sonar scanner images and install dependencies in Docker
make hooks-install    # install Docker-backed Git hooks
make shell            # interactive PHP container shell
```

### Local SonarQube setup

The local pre-scan sends analysis only to the shared SonarQube Community Build
service. Its server and persistent data live in Shared Docker, while the scanner
runs in the DTO Project Docker image and joins the `jooservices-shared` network
for that scan. Local analysis never reads the SonarCloud token and never uploads
to SonarQube Cloud. The shared server is opt-in and binds only to loopback.

On first use, start the server:

```bash
make sonar-up
```

Open <http://localhost:9000>. On first use, sign in with `admin` / `admin`,
immediately change the password, and save it under
`JOOSERVICES_SONAR_LOCAL_ADMIN_PASSWORD` in `Sites/.env`. Create a project with
key `jooservices_dto`, then generate a **Project analysis**
token for that project. Store the project token as
`JOOSERVICES_DTO_SONAR_LOCAL_TOKEN` in the ignored `Sites/.env` file:

```dotenv
JOOSERVICES_DTO_SONAR_LOCAL_TOKEN=<local-project-analysis-token>
```

Do not use the SonarCloud token for this key. `make sonar` starts the shared
server if it is stopped, waits for it to become ready, runs coverage analysis,
and fails when the local Quality Gate fails. Use `make sonar-stop` to stop the
shared server while preserving its named volumes. Other JOOservices projects can
use this server with their own analysis token under
`JOOSERVICES_<PROJECT>_SONAR_LOCAL_TOKEN` in `Sites/.env`.

GitHub Actions uses the repository `SONAR_TOKEN` secret for SonarCloud PR
analysis. That Cloud upload is confined to CI. The optional shared SonarQube
service reserves up to 4 GB RAM and keeps project data in named volumes.

| Command | Purpose |
| --- | --- |
| `make validate` | `composer validate --strict` |
| `make lint` | Pint, PHPCS, PHPStan, PHPMD, PHP-CS-Fixer |
| `make test` | PHPUnit (Unit + Integration) |
| `make test-coverage` | PHPUnit with Clover coverage |
| `make audit` | Composer audit |
| `make bench` | phpbench |
| `make ci` | lint + coverage run (local CI parity) |
| `make scan` | Gitleaks, OSV, Semgrep, Trivy, actionlint, zizmor, ShellCheck, and Hadolint in Project Docker |
| `make sonar-up` | Start the shared local SonarQube Community Build server |
| `make sonar` | Local SonarQube analysis and blocking quality-gate result; requires `JOOSERVICES_DTO_SONAR_LOCAL_TOKEN` in `Sites/.env` |
| `make sonar-stop` | Stop the shared SonarQube server without deleting its data |
| `make preflight` | Full local quality gate before commit |

Every linter runs at **maximum strictness with no ignore lists** — fix issues at the source instead of suppressing them.

Git hooks run `make preflight` before commits and scan pushed history against `origin/develop` before pushes. They fail closed when Docker tools, the local Sonar token, the GitHub token for online zizmor audits, or the base ref is missing, or when a blocking finding is reported. GitHub-side checks that need pull-request metadata or upload results still run on the PR.

IDE setup: Cursor / VS Code — install recommended workspace extensions; format-on-save runs Pint via `tools/pint` (Docker). PHPStorm — inspection profile + Pint file watcher.

## Community

- [Contributing guide](CONTRIBUTING.md) — setup, git workflow, commit convention, quality gates, PR rules
- [Security policy](SECURITY.md) — how to report vulnerabilities privately
- [Code of Conduct](CODE_OF_CONDUCT.md)
- [Support](SUPPORT.md)
- [Governance](GOVERNANCE.md)

## License

MIT — see [LICENSE](LICENSE).
