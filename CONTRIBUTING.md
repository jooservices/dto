# Contributing

Thank you for considering a contribution to `jooservices/dto`.

> [!IMPORTANT]
> This package is a **complete rebuild** (`v3.0.0`) with **no backward compatibility** with `v1.x` / `v2.x`.
> Contributions must target the new architecture — see [README › About v3.0.0](README.md#about-v300) and [CHANGELOG.md](CHANGELOG.md).

## Requirements

- Docker with Docker Compose v2, Git, Make, and curl
- No host PHP, Composer, or security scanner binaries are needed; the project runs its PHP tools and scanners in its own Compose services
- `JOOSERVICES_DTO_SONAR_LOCAL_TOKEN` in the ignored `Sites/.env` file for the full local gate (`make preflight`); it must be a DTO project analysis token from the shared local SonarQube instance
- A GitHub CLI login or `GH_TOKEN` environment variable for zizmor's online workflow audits

## Setup

```bash
make install          # build PHP + Sonar scanner images and install Composer dependencies in Docker
make hooks-install    # route Git hooks through the Docker-backed gate
make shell            # interactive PHP container shell
```

For first-time local Sonar setup, run `make sonar-up`, open
<http://localhost:9000>, sign in with `admin` / `admin`, immediately change the
password, and store it as `JOOSERVICES_SONAR_LOCAL_ADMIN_PASSWORD` in
`Sites/.env`. Create project key `jooservices_dto`, then create a **Project
analysis** token. Store it as `JOOSERVICES_DTO_SONAR_LOCAL_TOKEN` in the ignored
`Sites/.env`. The local
scanner runs in Project Docker, joins the shared Docker network for the scan,
and reads only this project token; it never reads the SonarCloud token. GitHub
Actions uses the repository `SONAR_TOKEN` secret for SonarCloud PR analysis.
Stop the shared server with `make sonar-stop` when it is no longer needed.

The project installs native Git hook scripts. The commit-msg hook validates Conventional Commits, the pre-commit hook runs the complete local gate, and the pre-push hook scans commits against `origin/develop` before running PHPUnit. PHP commands and scanners still run in Project Docker; host Git invokes the hooks. If the base ref is unavailable, the history scan fails; fetch it or set `DTO_GITLEAKS_BASE_REF` to the intended remote base.

Keep hooks enabled; **never bypass them with `--no-verify`**.

## Git workflow

- `master` — production; receives only approved release and hotfix merges
- `develop` — integration branch for normal work
- create `feature/*` / `fix/*` branches from the latest `develop`, then open the PR back into `develop`
- releases: cut `release/<version>` from `develop`, stabilize, PR into `master`, tag from `master`, merge `master` back into `develop`
- hotfixes: `hotfix/*` from `master`, merged back into both `master` and `develop`
- never commit directly to `develop` or `master`; every change arrives via PR with green checks

## Commit convention

Conventional Commits are enforced in three places:

- locally by the project `commit-msg` hook
- on every commit in a PR by `commitlint.yml`
- on PR titles by `semantic-pr.yml` (subject must start with an uppercase letter)

```text
feat(hydration): support named arguments in with()
fix(casting): reject INF and NaN in float caster
docs(readme): document key-space rules
```

Rules: imperative mood, uppercase first letter of the subject, no trailing period.

## Quality gates

Run the complete gate before every commit. The scanners run as one-shot Project Docker services against a read-only project mount; the PHP image does not need to contain scanner binaries. `make preflight` fails if a required scanner, image, credential, test, or quality gate is unavailable or reports a blocking finding.

| Command | Purpose |
| --- | --- |
| `make validate` | `composer validate --strict` |
| `make lint` | Pint (`per` preset), PHPCS (full `PSR12`), PHPStan (max level), PHPMD, PHP-CS-Fixer (PHPDoc-only) |
| `make test` | PHPUnit Unit + Integration suites |
| `make test-coverage` | Coverage run (CI enforces an 85% per-suite Clover floor) |
| `make audit` | Composer audit |
| `make bench` | phpbench |
| `make ci` | lint + coverage run (local CI parity) |
| `make scan` | Gitleaks staged + full history, OSV, Semgrep, Trivy filesystem + PHP image, actionlint, zizmor, ShellCheck, and Hadolint |
| `make sonar-up` | Start the on-demand Shared Docker SonarQube Community Build service |
| `make sonar` | Local SonarQube analysis with coverage and a blocking quality-gate result |
| `make sonar-stop` | Stop the shared SonarQube service while preserving its named volumes |
| `make preflight` | build + validation + lint + Unit/Integration tests + coverage floor + Composer audit + all scanners + Sonar quality gate |

The PR still has GitHub-side checks that require pull-request metadata or GitHub reporting (for example Dependency Review, CodeQL results, and Codecov upload). Local scans do not replace those required checks; the PR must still pass them.

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
- make sure every required CI check is green (validate → lint/test matrices and parallel security jobs → coverage upload)

## Reporting issues

- bugs and feature requests: [GitHub Issues](https://github.com/jooservices/dto/issues)
- **security vulnerabilities: never in public issues** — follow [SECURITY.md](SECURITY.md)

## AI-assisted contributors

AI and agent-assisted contributions must follow [AGENTS.md](AGENTS.md): inspect real repository state before changing anything, stop and ask when requirements conflict, and run the same quality gates as human contributors.

## License

By contributing you agree that your contributions are licensed under the [MIT License](LICENSE).
