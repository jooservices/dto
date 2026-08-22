# Contributing

Thank you for considering a contribution to `jooservices/dto`.

> [!IMPORTANT]
> This package is a **complete rebuild** (`v3.0.0`) with **no backward compatibility** with `v1.x` / `v2.x`.
> Contributions must target the new architecture — see [README › About v3.0.0](README.md#about-v300) and [CHANGELOG.md](CHANGELOG.md).

## Requirements

- PHP `>= 8.5`
- Docker with Docker Compose — **all** PHP tooling runs inside `php:8.5-cli-bookworm`; nothing needs to be installed locally
- Composer knowledge for day-to-day scripts (executed in the container)

## Setup

```bash
make build     # build the tooling image
make install   # composer install inside the container
make shell     # interactive container shell
```

CaptainHook git hooks are installed automatically via the Composer `post-install-cmd` / `post-update-cmd` scripts. **Never bypass hooks with `--no-verify`.**

## Git workflow

- `master` — production; receives only approved release and hotfix merges
- `develop` — integration branch for normal work
- create `feature/*` / `fix/*` branches from the latest `develop`, then open the PR back into `develop`
- releases: cut `release/<version>` from `develop`, stabilize, PR into `master`, tag from `master`, merge `master` back into `develop`
- hotfixes: `hotfix/*` from `master`, merged back into both `master` and `develop`
- never commit directly to `develop` or `master`; every change arrives via PR with green checks

## Commit convention

Conventional Commits are enforced in three places:

- locally by CaptainHook (`commit-msg`)
- on every commit in a PR by `commitlint.yml`
- on PR titles by `semantic-pr.yml` (subject must start with an uppercase letter)

```text
feat(hydration): support named arguments in with()
fix(casting): reject INF and NaN in float caster
docs(readme): document key-space rules
```

Rules: imperative mood, uppercase first letter of the subject, no trailing period.

## Quality gates

Run the relevant gates before every push — CI runs the same chain and **every job is required**:

| Command | Purpose |
| --- | --- |
| `make validate` | `composer validate --strict` |
| `make lint` | Pint (`per` preset), PHPCS (full `PSR12`), PHPStan (max level), PHPMD, PHP-CS-Fixer (PHPDoc-only) |
| `make test` | PHPUnit Unit + Integration suites |
| `make test-coverage` | Coverage run (CI enforces an 85% per-suite Clover floor) |
| `make audit` | Composer audit |
| `make bench` | phpbench |
| `make ci` | lint + coverage run (local CI parity) |

Coding rules:

- linters run at **maximum strictness with no ignore lists** — fix the source, do not suppress findings; new PHPStan `ignoreErrors` entries are not accepted
- when formatting rules conflict, **Pint wins**
- `declare(strict_types=1);` everywhere; follow existing module boundaries (`src/Core`, `src/Hydration`, `Casting`, …)
- keep changes consistent with SOLID, DRY, KISS, YAGNI; avoid unrelated refactors

## Testing expectations

- every behavior change ships with tests; bug fixes ship with a regression test named after the tracked defect ID (for example `C1HiddenPreservedOnWith`)
- unit and integration suites each independently stay above the 85% CI coverage floor
- public API changes update the README / relevant docs in the same PR

## Pull requests

A good PR explains:

- **what** changed
- **why** the change is needed
- **how** it was tested (include command output or test names)

Also:

- target `develop`
- keep the diff focused — no debug code, warnings, notices, or drive-by file churn
- make sure every required CI check is green (validate → lint → test → security → coverage upload)

## Reporting issues

- bugs and feature requests: [GitHub Issues](https://github.com/jooservices/dto/issues)
- **security vulnerabilities: never in public issues** — follow [SECURITY.md](SECURITY.md)

## AI-assisted contributors

AI and agent-assisted contributions must follow [AGENTS.md](AGENTS.md): inspect real repository state before changing anything, stop and ask when requirements conflict, and run the same quality gates as human contributors.

## License

By contributing you agree that your contributions are licensed under the [MIT License](LICENSE).
